<?php

namespace App\Http\Controllers;

use App\Models\StoreAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreAnnouncementController extends Controller
{
    /**
     * Display a listing of the announcements for the storekeeper.
     */
    public function index()
    {
        $announcements = StoreAnnouncement::with('creator')
            ->latest()
            ->paginate(10);

        return view('dashboard.storekeeper.announcements', compact('announcements'));
    }

    /**
     * Store a newly created announcement in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'target_role' => 'required|in:head_chef,bar_keeper,housekeeper,both,all',
            'expires_at' => 'nullable|date|after:now',
        ]);

        StoreAnnouncement::create([
            'message' => $request->message,
            'target_role' => $request->target_role,
            'created_by' => Auth::guard('staff')->id(),
            'expires_at' => $request->expires_at,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Announcement broadcasted successfully.');
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleStatus(StoreAnnouncement $announcement)
    {
        $announcement->update([
            'is_active' => !$announcement->is_active,
        ]);

        $status = $announcement->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Announcement $status successfully.");
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(StoreAnnouncement $announcement)
    {
        $announcement->delete();
        return redirect()->back()->with('success', 'Announcement deleted successfully.');
    }
}
