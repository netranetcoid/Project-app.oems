<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $companyId = session('company_id');
    $companyName = session('company_name', 'Company');

    $stats = [
      'company_name' => $companyName,
      'user_name' => $user?->name ?? '-',
      'user_email' => $user?->email ?? '-',
      'active_company_id' => $companyId,
      'roles' => $user ? $user->getRoleNames()->values() : collect(),
      'permissions_count' => $user ? $user->getAllPermissions()->count() : 0,
    ];

    return view('dashboard.dashboard_index', compact('stats'));
  }
}
