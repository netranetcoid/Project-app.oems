<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\View\View;

/** One entry point only; every card delegates to the established source module. */
class SettingsHubController extends Controller
{
    public function index(): View
    {
        return view('setting.hub.index', [
            'company' => Company::query()->findOrFail((int) session('company_id')),
        ]);
    }
}
