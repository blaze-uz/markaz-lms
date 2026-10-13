<?php

namespace App\Http\Controllers;

class ReportsController extends Controller
{
    public function debts()
    {
        return view('admin.reports.debts');
    }
}
