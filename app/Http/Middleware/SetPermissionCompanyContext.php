<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionCompanyContext
{
  public function handle(Request $request, Closure $next): Response
  {
    $companyId = session('company_id');

    if (!$companyId) {
      return redirect()->route('company.select');
    }

    if (function_exists('setPermissionsTeamId')) {
      setPermissionsTeamId((int) $companyId);
    }

    return $next($request);
  }
}
