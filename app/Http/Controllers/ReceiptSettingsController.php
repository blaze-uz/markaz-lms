<?php

namespace App\Http\Controllers;

class ReceiptSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.receipts.index');
    }
}