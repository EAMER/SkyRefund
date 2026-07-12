<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function export(Request $request)
    {
        $type = $request->get('type', 'daily');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Refund::query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $rows = match ($type) {
            'weekly' => $query->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('WEEK(created_at, 1) as week'),
                DB::raw('COUNT(*) as total_refunds')
            )->groupBy('year', 'week')->orderBy('year')->orderBy('week')->get(),
            'monthly' => $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total_refunds')
            )->groupBy('month')->orderBy('month')->get(),
            'airline' => $query->select('airline_id', DB::raw('COUNT(*) as total_refunds'))
                ->with('airline')
                ->groupBy('airline_id')
                ->orderByDesc('total_refunds')
                ->get(),
            'department' => $query->select('current_department', DB::raw('COUNT(*) as total_refunds'))
                ->groupBy('current_department')
                ->orderByDesc('total_refunds')
                ->get(),
            'resolution' => $query->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_resolution_minutes'))->first(),
            default => $query->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(*) as total_refunds')
            )->groupBy('day')->orderBy('day')->get(),
        };

        $filename = 'refund-report-' . $type . '-' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows, $type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'resolution') {
                fputcsv($handle, ['avg_resolution_minutes']);
                fputcsv($handle, [$rows->avg_resolution_minutes ?? 0]);
            } elseif ($type === 'airline') {
                fputcsv($handle, ['airline_id', 'airline_name', 'total_refunds']);
                foreach ($rows as $row) {
                    $name = optional($row->airline)->name ?? 'Unknown';
                    fputcsv($handle, [$row->airline_id, $name, $row->total_refunds]);
                }
            } elseif ($type === 'department') {
                fputcsv($handle, ['current_department', 'total_refunds']);
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->current_department, $row->total_refunds]);
                }
            } else {
                $columns = match ($type) {
                    'weekly' => ['year', 'week', 'total_refunds'],
                    'monthly' => ['month', 'total_refunds'],
                    default => ['day', 'total_refunds'],
                };
                fputcsv($handle, $columns);
                foreach ($rows as $row) {
                    fputcsv($handle, array_values((array) $row));
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
