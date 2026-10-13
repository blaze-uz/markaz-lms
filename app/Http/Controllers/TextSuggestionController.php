<?php

namespace App\Http\Controllers;

use App\Services\TextSuggestionService;
use Illuminate\Http\Request;

class TextSuggestionController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new TextSuggestionService();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text' => 'required|string',
            'type' => 'required|string',
        ]);

        $this->service->store($data);

        session()->flash("success_alert", __('translation.alert_messages.created'));
        return back();
    }

    public function delete($id)
    {
        $this->service->delete($id);

        session()->flash("success_alert", __('translation.alert_messages.deleted'));
        return response()->json(['success' => true]);
    }
}
