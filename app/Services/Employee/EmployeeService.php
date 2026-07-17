<?php

namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(
        protected EmployeePhotoService $photoService,
        protected EmployeeUserService $userService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Employee Number
    |--------------------------------------------------------------------------
    */

    protected function generateEmployeeNo(int $companyId): string
    {
        $last = Employee::query()
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->first();

        if (!$last) {
            return 'EMP-00001';
        }

        $number = (int) preg_replace(
            '/[^0-9]/',
            '',
            $last->employee_no
        );

        return 'EMP-' . str_pad(
            $number + 1,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(array $data): Employee
    {
        return DB::transaction(function () use ($data) {

            $companyId = session('company_id');
            if (!$companyId) {
                throw new \RuntimeException('Company aktif belum dipilih.');
            }

            // Field form ini bukan kolom employees; simpan dulu untuk proses
            // akun login lalu keluarkan sebelum insert agar tidak SQL error.
            $createLogin = (bool) ($data['create_login'] ?? false);
            $role = $data['role'] ?? null;
            unset($data['create_login'], $data['role']);

            $data['company_id'] = $companyId;

            /*
            |--------------------------------------------------------------------------
            | Employee Number
            |--------------------------------------------------------------------------
            */

            if (empty($data['employee_no'])) {

                $data['employee_no'] = $this->generateEmployeeNo(
                    $companyId
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Default Status
            |--------------------------------------------------------------------------
            */

            $data['work_status'] ??= 'active';

            $data['employment_status'] ??= 'permanent';

            /*
            |--------------------------------------------------------------------------
            | Upload Photo
            |--------------------------------------------------------------------------
            */

            if (
                !empty($data['photo'])
            ) {

                $data['photo'] = $this->photoService->upload(

                    $data['photo'],

                    $companyId

                );

            }

            /*
            |--------------------------------------------------------------------------
            | Create Employee
            |--------------------------------------------------------------------------
            */

            $employee = Employee::create($data);

            if ($createLogin && !empty($employee->email)) {
                $user = $this->userService->create($employee->toArray());
                $employee->update(['user_id' => $user->id]);
                // Role akan dipasang oleh modul akses terpusat setelah
                // company context aktif; tidak mengubah owner/developer.
                if ($role) {
                    $user->syncRoles([$role]);
                }
            }

            return $employee;

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */
    public function update(
    Employee $employee,
    array $data
): Employee {

    return DB::transaction(function () use (
        $employee,
        $data
    ) {

        $companyId = session('company_id');
        if (!$companyId) {
            throw new \RuntimeException('Company aktif belum dipilih.');
        }

        $createLogin = (bool) ($data['create_login'] ?? false);
        $role = $data['role'] ?? null;
        unset($data['create_login'], $data['role']);

        /*
        |--------------------------------------------------------------------------
        | Replace Photo
        |--------------------------------------------------------------------------
        */

        if (!empty($data['photo'])) {

            $data['photo'] = $this->photoService->replace(

                $employee->photo,

                $data['photo'],

                $companyId

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Company Protection
        |--------------------------------------------------------------------------
        */

        unset($data['company_id']);

        /*
        |--------------------------------------------------------------------------
        | Update Employee
        |--------------------------------------------------------------------------
        */

        $employee->update($data);

        if ($createLogin && !empty($employee->email)) {
            $user = $employee->user;
            if ($user) {
                $this->userService->update($user, $employee->toArray());
            } else {
                $user = $this->userService->create($employee->toArray());
                $employee->update(['user_id' => $user->id]);
            }
            if ($role) {
                $user->syncRoles([$role]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Reload Relationship
        |--------------------------------------------------------------------------
        */

        return $employee->fresh([
            'branch',
            'division',
            'position',
            'supervisor',
            'manager',
        ]);

    });

}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/
    public function delete(
        Employee $employee
    ): void {

        DB::transaction(function () use ($employee) {

            /*
            |--------------------------------------------------------------------------
            | Delete Photo
            |--------------------------------------------------------------------------
            */

            if (!empty($employee->photo)) {

                $this->photoService->delete(
                    $employee->photo
                );

            }

            /*
            |--------------------------------------------------------------------------
            | Soft Delete Employee
            |--------------------------------------------------------------------------
            */

            $employee->delete();

        });

    }
}
