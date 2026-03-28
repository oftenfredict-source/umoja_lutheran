<?php

namespace App\Http\Controllers;

use App\Models\StockReturn;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockReturnController extends Controller
{
    /**
     * Housekeeper: View returnable items currently held and pending returns.
     */
    public function housekeeperIndex()
    {
        $staff = Auth::guard('staff')->user();

        // Get all completed stock requests for this housekeeper involving returnable products
        $heldItems = StockRequest::with(['productVariant.product'])
            ->where('requested_by', $staff->id)
            ->where('status', 'completed')
            ->whereHas('productVariant.product', fn($q) => $q->where('is_returnable', true))
            ->get()
            ->map(function ($req) use ($staff) {
                // Calculate how many have already been returned (received)
                $returned = StockReturn::where('staff_id', $staff->id)
                    ->where('product_variant_id', $req->product_variant_id)
                    ->where('status', 'received')
                    ->sum('quantity');

                $pending = StockReturn::where('staff_id', $staff->id)
                    ->where('product_variant_id', $req->product_variant_id)
                    ->where('status', 'pending')
                    ->sum('quantity');

                $req->quantity_held = max(0, $req->quantity - $returned - $pending);
                $req->quantity_returned = $returned;
                $req->quantity_pending_return = $pending;
                return $req;
            })
            ->filter(fn($r) => $r->quantity_held > 0 || $r->quantity_pending_return > 0)
            ->values();

        // My return history
        $myReturns = StockReturn::with(['productVariant.product'])
            ->where('staff_id', $staff->id)
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard.stock-returns.housekeeper-index', compact('heldItems', 'myReturns', 'staff'));
    }

    /**
     * Housekeeper: Submit a return request.
     */
    public function store(Request $request)
    {
        $staff = Auth::guard('staff')->user();

        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verify the product is returnable
        $variant = ProductVariant::with('product')->findOrFail($request->product_variant_id);
        if (!$variant->product->is_returnable) {
            return response()->json(['success' => false, 'message' => 'This item is not returnable.'], 422);
        }

        StockReturn::create([
            'staff_id' => $staff->id,
            'product_variant_id' => $request->product_variant_id,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Return request submitted. Awaiting storekeeper.']);
        }

        return redirect()->route('stock-returns.housekeeper-index')
            ->with('success', 'Return request submitted successfully.');
    }

    /**
     * Storekeeper: View pending return requests.
     */
    public function storekeeperIndex()
    {
        $pendingReturns = StockReturn::with(['staff', 'productVariant.product'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $recentlyReceived = StockReturn::with(['staff', 'productVariant.product', 'receiver'])
            ->where('status', 'received')
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard.stock-returns.storekeeper-index', compact('pendingReturns', 'recentlyReceived'));
    }

    /**
     * Storekeeper: Confirm receipt of a returned item (restocks inventory).
     */
    public function receive(Request $request, StockReturn $stockReturn)
    {
        $staff = Auth::guard('staff')->user();

        if ($stockReturn->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This return has already been processed.'], 422);
        }

        $stockReturn->update([
            'status' => 'received',
            'received_by' => $staff->id,
            'received_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item received back into store. Inventory updated.',
        ]);
    }

    /**
     * Storekeeper: Reject a return request.
     */
    public function reject(Request $request, StockReturn $stockReturn)
    {
        $staff = Auth::guard('staff')->user();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($stockReturn->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'This return has already been processed.'], 422);
        }

        $stockReturn->update([
            'status' => 'rejected',
            'received_by' => $staff->id,
            'received_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return rejected.',
        ]);
    }
}
