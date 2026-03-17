<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a list of system activities.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Optional filtering by user or action
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->action) {
            $query->where('action', $request->action);
        }

        return response()->json($query->paginate(20));
    }
}