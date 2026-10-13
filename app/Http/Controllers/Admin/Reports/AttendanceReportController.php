<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:report-attendance');
    }

    public function index()
    {
        return view('admin.reports.attendance.index');
    }
}
