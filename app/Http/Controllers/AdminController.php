<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function clearCache(Request $request)
    {
        //get cache key and forget it
        $key = $request->input('key');
        cache()->forget($key);

        session()->flash('success', __('words.refreshed_successfully'));

        return redirect()->back();
    }

    public function privacyPolicy()
    {
        $effective_date = Settings::query()->orderBy('created_at')->first()?->created_at;
        $name = settings('name');
        $site_url = config('app.url');
        $address = settings('address');
        $phone = settings('phone');

        return view('privacy-policy', compact('effective_date', 'site_url', 'address', 'phone', 'name'));
    }
}
