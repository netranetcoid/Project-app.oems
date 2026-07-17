<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureCompanySelected
{
  public function handle(Request $request, Closure $next): Response
  {
    if (!$request->user()) {
      return redirect()->route('login');
    }

    if ($request->routeIs('company.select', 'company.set', 'logout')) {
      return $next($request);
    }

    $companyId = session('company_id');

    if (!$companyId) {
      return redirect()->route('company.select');
    }

    $company = Company::query()
      ->whereKey($companyId)
      ->active()
      ->first();

    if (!$company) {
      $this->clearCompanySession($request);

      return redirect()
        ->route('company.select')
        ->withErrors(['company_id' => 'Company aktif tidak valid. Silakan pilih ulang.']);
    }

    if (!$this->userBelongsToCompany($request->user(), (int) $companyId)) {
      $this->clearCompanySession($request);

      return redirect()
        ->route('company.select')
        ->withErrors(['company_id' => 'Kamu tidak punya akses ke company tersebut.']);
    }

    return $next($request);
  }

  private function clearCompanySession(Request $request): void
  {
    $request->session()->forget([
      'company_id',
      'company_name',
    ]);

    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId(null);
    }
  }

  private function userBelongsToCompany($user, int $companyId): bool
  {
    try {
      if (method_exists($user, 'activeCompanies')) {
        if ($user->activeCompanies()
          ->where('companies.id', $companyId)
          ->exists()) {
          return true;
        }
      }
    } catch (Throwable $e) {
      report($e);
    }

    if (Schema::hasColumn('users', 'company_id')) {
      return (int) $user->company_id === $companyId;
    }

    return false;
  }
}
