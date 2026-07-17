<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompensationController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $employees = Employee::forCompany($companyId)
            ->with(['branch', 'division', 'position'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%' . trim((string) $request->input('q')) . '%';
                $query->where(fn ($sub) => $sub->where('name', 'like', $term)->orWhere('employee_no', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('hr.compensation.index', compact('employees'));
    }
}
