<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.sms');
    }
}
