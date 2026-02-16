<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    //

    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(20);

        return response()->json($logs);
    }

}
