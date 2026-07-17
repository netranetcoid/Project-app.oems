<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\StoreUserRequest;
use App\Http\Requests\System\UpdateUserRequest;
use App\Models\Employee;
use App\Models\User;
use App\Services\System\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $companyId = session('company_id');

        $users = User::query()
            ->with([
                'employee',
                'branch',
                'division',
                'position',
            ])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(20);

        return view(
            'system.users.index',
            compact('users')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $companyId = session('company_id');

        $employees = Employee::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->where(function ($q) use ($companyId) {

                $q->whereNull('company_id')
                  ->orWhere('company_id', $companyId);

            })
            ->orderBy('name')
            ->get();

        return view(
            'system.users.create',
            compact(
                'employees',
                'roles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreUserRequest $request
    ): RedirectResponse {

        $this->userService->store(
            $request->validated()
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dibuat.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
        public function edit(
        User $user
    ): View {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $companyId = session('company_id');

        $employees = Employee::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->where(function ($query) use ($companyId) {

                $query->whereNull('company_id')
                      ->orWhere('company_id', $companyId);

            })
            ->orderBy('name')
            ->get();

        $user->load([
            'employee',
            'branch',
            'division',
            'position',
        ]);

        return view(
            'system.users.edit',
            compact(
                'user',
                'employees',
                'roles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $this->userService->update(
            $user,
            $request->validated()
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        User $user
    ): RedirectResponse {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $this->userService->delete(
            $user
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil dihapus.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        User $user
    ): RedirectResponse {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $this->userService->resetPassword($user);

        return back()->with(
            'success',
            'Password berhasil direset ke default.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOCK USER
    |--------------------------------------------------------------------------
    */

    public function lock(
        User $user
    ): RedirectResponse {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $this->userService->lock($user);

        return back()->with(
            'success',
            'User berhasil dikunci.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UNLOCK USER
    |--------------------------------------------------------------------------
    */

    public function unlock(
        User $user
    ): RedirectResponse {

        abort_if(
            $user->company_id != session('company_id'),
            403
        );

        $this->userService->unlock($user);

        return back()->with(
            'success',
            'User berhasil dibuka.'
        );
    }
}