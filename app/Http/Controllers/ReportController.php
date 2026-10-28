<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['Administrator', 'Staff'], true), 403);

        $students = User::query()->where('role', 'Student');
        $staff = User::query()->where('role', 'Staff');
        $admins = User::query()->where('role', 'Administrator');
        $publishedEvents = Event::query()->where('status', 'Published');
        $draftEvents = Event::query()->where('status', 'Draft');

        $report = [
            'total_users' => User::count(),
            'total_students' => $students->count(),
            'total_staff' => $staff->count(),
            'total_admins' => $admins->count(),
            'total_events' => Event::count(),
            'published_events' => $publishedEvents->count(),
            'draft_events' => $draftEvents->count(),
            'active_students' => $students->clone()->where('status', 'Active')->count(),
            'graduated_students' => $students->clone()->where('status', 'Graduated')->count(),
            'inactive_students' => $students->clone()->where('status', 'Inactive')->count(),
        ];

        return view('reports.index', [
            'report' => $report,
            'students' => $students->latest()->limit(10)->get(),
            'events' => Event::query()->latest()->limit(10)->get(),
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['Administrator', 'Staff'], true), 403);

        $students = User::query()->where('role', 'Student');
        $staff = User::query()->where('role', 'Staff');
        $admins = User::query()->where('role', 'Administrator');
        $publishedEvents = Event::query()->where('status', 'Published');
        $draftEvents = Event::query()->where('status', 'Draft');

        $summary = [
            ['metric', 'value'],
            ['total_users', User::count()],
            ['total_students', $students->count()],
            ['total_staff', $staff->count()],
            ['total_admins', $admins->count()],
            ['total_events', Event::count()],
            ['published_events', $publishedEvents->count()],
            ['draft_events', $draftEvents->count()],
            ['active_students', $students->clone()->where('status', 'Active')->count()],
            ['inactive_students', $students->clone()->where('status', 'Inactive')->count()],
            ['graduated_students', $students->clone()->where('status', 'Graduated')->count()],
        ];

        $rows = User::query()->select(['id', 'name', 'email', 'role', 'student_number', 'phone', 'program', 'year_level', 'status', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = fopen('php://temp', 'w+');

        foreach ($summary as $row) {
            fputcsv($csv, $row);
        }

        fputcsv($csv, []);
        fputcsv($csv, ['id', 'name', 'email', 'role', 'student_number', 'phone', 'program', 'year_level', 'status', 'created_at']);

        foreach ($rows as $row) {
            fputcsv($csv, [
                $row->id,
                $row->name,
                $row->email,
                $row->role,
                $row->student_number,
                $row->phone,
                $row->program,
                $row->year_level,
                $row->status,
                $row->created_at?->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="system-report.csv"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['Administrator', 'Staff'], true), 403);

        $students = User::query()->where('role', 'Student');
        $staff = User::query()->where('role', 'Staff');
        $admins = User::query()->where('role', 'Administrator');
        $publishedEvents = Event::query()->where('status', 'Published');
        $draftEvents = Event::query()->where('status', 'Draft');

        $report = [
            'total_users' => User::count(),
            'total_students' => $students->count(),
            'total_staff' => $staff->count(),
            'total_admins' => $admins->count(),
            'total_events' => Event::count(),
            'published_events' => $publishedEvents->count(),
            'draft_events' => $draftEvents->count(),
            'active_students' => $students->clone()->where('status', 'Active')->count(),
            'inactive_students' => $students->clone()->where('status', 'Inactive')->count(),
            'graduated_students' => $students->clone()->where('status', 'Graduated')->count(),
        ];

        $users = User::query()
            ->select(['name', 'email', 'role', 'student_number', 'status', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Pdf::loadView('reports.pdf', compact('report', 'users'))
            ->download('system-report.pdf');
    }
}
