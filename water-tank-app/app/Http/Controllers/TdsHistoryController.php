<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorReading;
use Carbon\Carbon;

class TdsHistoryController extends Controller
{
    /**
     * Show the history page (Blade + JS).
     */
    public function show()
    {
        return view('tds_history');
    }

    /**
     * Return paginated JSON for chart & table.
     */
    public function index(Request $request)
    {
        // 1) sanitize days
        $days = max(1, min((int)$request->query('days', 1), 365));
        $start = Carbon::now()->subDays($days);

        // 2) grab *all* rows, no paginate()
        $rows = SensorReading::where('reading_at', '>=', $start)
            ->orderBy('reading_at', 'asc')
            ->get(['reading_at', 'tds_ppm']);

        // 3) wrap in `data` so DataTables sees it
        return response()->json([
            'data' => $rows
        ]);
    }
}
