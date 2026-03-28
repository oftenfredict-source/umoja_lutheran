<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\PurchaseRequest;
use App\Models\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountantController extends Controller
{
    /**
     * Accountant Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'pending_approval' => ShoppingList::where('status', 'pending')->count(),
            'checked_lists' => ShoppingList::where('status', 'accountant_checked')->count(),
            'approved_lists' => ShoppingList::where('status', 'approved')->count(),
            'ready_lists' => ShoppingList::where('status', 'ready_for_purchase')->count(),
            'purchased_lists' => ShoppingList::where('status', 'purchased')->count(),
            'total_spent' => ShoppingList::where('status', 'completed')->sum('total_actual_cost'),
            'total_day_services_revenue' => \App\Models\DayService::where('payment_status', 'paid')
                ->whereDate('service_date', now()->toDateString())
                ->sum('amount_paid'),
        ];

        // Shopping lists awaiting accountant approval
        $pendingLists = ShoppingList::with('items')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // Recently approved & completed
        $recentLists = ShoppingList::with('items')
            ->whereIn('status', ['approved', 'ready_for_purchase', 'completed'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.accountant.dashboard', compact('stats', 'pendingLists', 'recentLists'));
    }

    /**
     * Shopping list approvals — the core accountant duty
     */
    public function shoppingLists(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $query = ShoppingList::with('items');

        if ($tab === 'pending') {
            $query->where('status', 'pending');
        } elseif ($tab === 'approved') {
            // Include accountant_checked (sent to manager) and approved (manager approved, awaiting disbursement)
            $query->whereIn('status', ['accountant_checked', 'approved']);
        } elseif ($tab === 'purchased') {
            // Include lists that are ready for purchase or already completed/purchased
            $query->whereIn('status', ['ready_for_purchase', 'purchased', 'completed']);
        }

        $lists = $query->latest()->paginate(20);

        return view('dashboard.accountant.shopping-lists', compact('lists', 'tab'));
    }

    /**
     * Show a single shopping list for review
     */
    public function showShoppingList(ShoppingList $shoppingList)
    {
        $shoppingList->load('items');
        return view('dashboard.accountant.shopping-list-show', compact('shoppingList'));
    }

    /**
     * Approve a shopping list
     */
    public function approveShoppingList(Request $request, ShoppingList $shoppingList)
    {
        $request->validate([
            'budget_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shoppingList->update([
            'status' => 'accountant_checked',
            'budget_amount' => $request->budget_amount,
            'notes' => $request->notes ?? $shoppingList->notes,
        ]);

        return back()->with('success', 'Shopping list approved with budget of TZS ' . number_format($request->budget_amount));
    }

    /**
     * Disburse funds for an approved shopping list
     */
    public function disburseFunds(Request $request, ShoppingList $shoppingList)
    {
        if ($shoppingList->status !== 'approved') {
            return back()->with('error', 'Only approved lists can have funds disbursed.');
        }

        $shoppingList->update([
            'status' => 'ready_for_purchase',
            'notes' => $shoppingList->notes . ' | FUNDS DISBURSED by Accountant on ' . now()->format('d M Y H:i'),
        ]);

        return back()->with('success', 'Funds disbursed. The Storekeeper can now proceed with the purchase.');
    }

    /**
     * Reject a shopping list
     */
    public function rejectShoppingList(Request $request, ShoppingList $shoppingList)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $shoppingList->update([
            'status' => 'rejected',
            'notes' => 'REJECTED: ' . $request->rejection_reason,
        ]);

        return back()->with('success', 'Shopping list rejected.');
    }

    /**
     * Purchase Payment Verification
     * Lists all shopping lists that have been purchased and need payment verified
     */
    public function paymentVerification(Request $request)
    {
        $tab = $request->get('tab', 'unverified');

        $query = ShoppingList::with('items')->where('status', 'purchased');

        if ($tab === 'unverified') {
            // Not yet verified — no payment_verified_at (we track this with a notes check or a dedicated field)
            // Since there's no payment_verified field, we use a status marker: 'purchased' = unverified, 'completed' = verified
            $query->where('status', 'purchased');
        } elseif ($tab === 'verified') {
            $query = ShoppingList::with('items')->where('status', 'completed');
        }

        $lists = $query->latest()->paginate(20);

        return view('dashboard.accountant.payment-verification', compact('lists', 'tab'));
    }

    /**
     * Mark payment as verified for a shopping list
     */
    public function verifyPayment(Request $request, ShoppingList $shoppingList)
    {
        // Strip commas from numeric input
        $actualCost = $request->actual_cost;
        if (is_string($actualCost)) {
            $actualCost = (float) str_replace(',', '', $actualCost);
        }

        $shoppingList->update([
            'status' => 'completed',
            'total_actual_cost' => $actualCost,
            'amount_used' => $actualCost,
            'amount_remaining' => max(0, ($shoppingList->budget_amount ?? 0) - $actualCost),
            'notes' => $shoppingList->notes . ' | PAYMENT VERIFIED by Accountant: ' . ($request->payment_notes ?? ''),
        ]);

        // NOTE: Stock is already tracked via shopping_list_items (purchased_quantity / received_quantity_kg).
        // The StorekeeperController sums those fields directly for inventory levels.
        // Creating StockReceipt records here would cause DOUBLE-COUNTING in the Main Store.

        return back()->with('success', 'Payment of TZS ' . number_format($request->actual_cost) . ' verified successfully.');
    }

    /**
     * Financial summary report
     */
    public function reports(Request $request)
    {
        $fromDate = $request->get('from', now()->startOfMonth()->toDateString());
        $toDate = $request->get('to', now()->toDateString());

        $summary = [
            'total_approved_budget' => ShoppingList::whereIn('status', ['approved', 'on_list', 'purchased', 'completed'])
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->sum('budget_amount'),
            'total_spent' => ShoppingList::whereIn('status', ['purchased', 'completed'])
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->sum('total_actual_cost'),
            'total_verified' => ShoppingList::where('status', 'completed')
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->sum('total_actual_cost'),
            'lists_count' => ShoppingList::whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])->count(),
        ];

        $lists = ShoppingList::with('items')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->latest()
            ->paginate(20);

        return view('dashboard.accountant.reports', compact('summary', 'lists', 'fromDate', 'toDate'));
    }

    /**
     * Day Services Revenue Dashboard for Accountant
     * Shows daily grouped revenue from reception that needs verification
     */
    public function dayServicesRevenue(Request $request)
    {
        $tab = $request->get('tab', 'unverified');

        // Build the base query: Group by date
        $query = \App\Models\DayService::select(
            'service_date',
            DB::raw('COUNT(id) as total_services'),
            DB::raw('SUM(CASE WHEN payment_status = "paid" THEN amount_paid ELSE 0 END) as total_revenue'),
            DB::raw('SUM(CASE WHEN payment_status != "paid" THEN amount ELSE 0 END) as pending_revenue'),
            DB::raw('MAX(accountant_verified_at) as verified_at'), // Will be null if any are unverified
            DB::raw('SUM(CASE WHEN payment_status = "paid" AND accountant_verified_at IS NULL THEN 1 ELSE 0 END) as unverified_count')
        )
            ->groupBy('service_date')
            ->orderByDesc('service_date');

        if ($tab === 'unverified') {
            // Show days that have at least one paid but unverified service
            $query->havingRaw('unverified_count > 0');
        } elseif ($tab === 'verified') {
            // Show days where all paid services have been verified (unverified_count = 0) AND there is at least some revenue
            $query->havingRaw('unverified_count = 0')->havingRaw('total_revenue > 0');
        }

        $dailyRevenues = $query->paginate(15);

        // Overall stats
        $stats = [
            'unverified_days' => \App\Models\DayService::select('service_date')
                ->where('payment_status', 'paid')
                ->whereNull('accountant_verified_at')
                ->groupBy('service_date')
                ->get()
                ->count(),
            'total_unverified_cash' => \App\Models\DayService::where('payment_status', 'paid')
                ->whereNull('accountant_verified_at')
                ->sum('amount_paid'),
        ];

        return view('dashboard.accountant.day-services-revenue', compact('dailyRevenues', 'tab', 'stats'));
    }

    /**
     * Verify all Day Services revenue for a specific date
     */
    public function verifyDayServicesRevenue(Request $request)
    {
        $request->validate([
            'service_date' => 'required|date',
        ]);

        $date = $request->service_date;

        // Find all paid, unverified services for this date
        $affectedRows = \App\Models\DayService::where('service_date', $date)
            ->where('payment_status', 'paid')
            ->whereNull('accountant_verified_at')
            ->update([
                'accountant_verified_at' => now(),
                'accountant_id' => Auth::guard('staff')->id(),
            ]);

        if ($affectedRows > 0) {
            return back()->with('success', "Revenue for " . \Carbon\Carbon::parse($date)->format('M d, Y') . " verified successfully. ($affectedRows services marked as received)");
        }

        return back()->with('info', "No unverified paid services found for " . \Carbon\Carbon::parse($date)->format('M d, Y') . ".");
    }
}
