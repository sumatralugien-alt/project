<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Report</title>
    <style>
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #0f766e; font-size: 22px; margin-bottom: 4px; }
        .muted { color: #64748b; }
        .summary { margin: 18px 0; width: 100%; }
        .summary td { background: #f1f5f9; padding: 9px; width: 25%; }
        .label { color: #64748b; display: block; font-size: 9px; }
        .value { font-size: 16px; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #0f766e; color: #fff; text-align: left; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; }
        tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Operations Report</h1>
    <p class="muted">Generated {{ now()->format('Y-m-d H:i') }}</p>

    <table class="summary">
        <tr>
            <td><span class="label">Total users</span><span class="value">{{ $report['total_users'] }}</span></td>
            <td><span class="label">Students</span><span class="value">{{ $report['total_students'] }}</span></td>
            <td><span class="label">Staff</span><span class="value">{{ $report['total_staff'] }}</span></td>
            <td><span class="label">Events</span><span class="value">{{ $report['total_events'] }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Active students</span><span class="value">{{ $report['active_students'] }}</span></td>
            <td><span class="label">Inactive students</span><span class="value">{{ $report['inactive_students'] }}</span></td>
            <td><span class="label">Graduated students</span><span class="value">{{ $report['graduated_students'] }}</span></td>
            <td><span class="label">Published events</span><span class="value">{{ $report['published_events'] }}</span></td>
        </tr>
    </table>

    <h2>Users</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Student ID</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->student_number ?? '-' }}</td>
                    <td>{{ $user->status ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>