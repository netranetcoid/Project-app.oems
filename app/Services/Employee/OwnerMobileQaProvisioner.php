<?php

namespace App\Services\Employee;

use App\Models\AttendanceLocationPolicy;
use App\Models\AttendanceShift;
use App\Models\AttendanceShiftAssignment;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Creates one intentional Owner QA identity. A normal Owner web account alone
 * cannot use OvallHR because mobile functions are tied to an active employee.
 * This service links both records, supplies a flexible QA shift, and creates
 * an anywhere policy only for the QA division—not for all management staff.
 */
class OwnerMobileQaProvisioner
{
    public function provision(string $name, string $email, string $username, string $password): array
    {
        $company = Company::query()->where('code', 'OEMS')->firstOrFail();
        $branch = Branch::query()->forCompany($company->id)->where('code', 'HO')->first()
            ?: Branch::query()->forCompany($company->id)->active()->first();

        return DB::transaction(function () use ($company, $branch, $name, $email, $username, $password): array {
            // Division/Position di proyek lama tidak memakai trait SoftDeletes
            // meskipun tabelnya memiliki kolom deleted_at; jangan panggil
            // withTrashed()/restore() pada model tersebut.
            $division = Division::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'MOBILE_QA'],
                [
                    'name' => 'Mobile QA / Owner Test', 'type' => 'qa', 'sort_order' => 999,
                    'is_kpi_enabled' => false, 'is_payroll_enabled' => false,
                    'is_attendance_required' => true, 'status' => 'active',
                    'notes' => 'Scope khusus pengujian OvallHR oleh Owner. Jangan dipakai sebagai divisi operasional.',
                ]
            );
            // `deleted_at` tetap dinolkan agar entri lama yang pernah
            // dinonaktifkan dapat dipakai ulang oleh akun QA yang sama.
            $division->update(['name' => 'Mobile QA / Owner Test', 'status' => 'active', 'deleted_at' => null]);

            $position = Position::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => 'OWNER_QA'],
                [
                    'division_id' => $division->id, 'name' => 'Owner QA', 'level' => 100,
                    'type' => 'owner', 'is_management' => true, 'is_approver' => true,
                    'is_kpi_enabled' => false, 'is_payroll_enabled' => false, 'status' => 'active',
                    'notes' => 'Jabatan akun uji OvallHR milik Owner.',
                ]
            );
            $position->update([
                'division_id' => $division->id,
                'name' => 'Owner QA',
                'status' => 'active',
                'deleted_at' => null,
            ]);

            $user = User::query()->withTrashed()->where('email', $email)->first();
            $attributes = [
                'company_id' => $company->id, 'branch_id' => $branch?->id, 'division_id' => $division->id,
                'position_id' => $position->id, 'name' => $name, 'username' => $username,
                'email' => $email, 'password' => Hash::make($password), 'password_changed_at' => now(),
                'is_owner' => true, 'is_developer' => false, 'is_super_admin' => false,
                'is_active' => true, 'is_locked' => false, 'status' => 'active', 'deleted_at' => null,
            ];
            if ($user) {
                $user->forceFill($attributes)->save();
            } else {
                $user = User::query()->create($attributes);
            }

            $employee = Employee::query()->withTrashed()->where('company_id', $company->id)
                ->where('employee_no', 'OSM-OWNER-QA')->first();
            $employeeAttributes = [
                'company_id' => $company->id, 'user_id' => $user->id, 'branch_id' => $branch?->id,
                'division_id' => $division->id, 'position_id' => $position->id,
                'employee_no' => 'OSM-OWNER-QA', 'name' => $name, 'nickname' => 'Owner QA', 'email' => $email,
                'employment_status' => 'permanent', 'work_status' => 'active', 'join_date' => now()->toDateString(),
                'is_attendance_required' => true, 'attendance_type' => 'flexible', 'work_location_type' => 'field',
                'is_kpi_enabled' => false, 'basic_salary' => 0, 'notes' => 'Akun QA Owner untuk menguji OvallHR. Bukan data payroll operasional.',
                'deleted_at' => null,
            ];
            if ($employee) {
                $employee->forceFill($employeeAttributes)->save();
            } else {
                $employee = Employee::query()->create($employeeAttributes);
            }
            $user->update(['employee_id' => $employee->id]);

            DB::table('company_user')->updateOrInsert(
                ['company_id' => $company->id, 'user_id' => $user->id],
                ['is_default' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            if (function_exists('setPermissionsTeamId')) setPermissionsTeamId($company->id);
            $ownerRole = Role::query()->where('company_id', $company->id)->where('name', 'owner')->firstOrFail();
            DB::table('model_has_roles')->where(['company_id' => $company->id, 'model_type' => User::class, 'model_id' => $user->id])->delete();
            DB::table('model_has_roles')->insert(['role_id' => $ownerRole->id, 'model_type' => User::class, 'model_id' => $user->id, 'company_id' => $company->id]);

            $shiftAttributes = [
                'name' => 'Owner QA Flexible', 'work_type' => 'flexible', 'clock_in_time' => '08:00', 'clock_out_time' => '17:00',
                'work_hours' => 8, 'grace_in_minutes' => 720, 'allow_overtime' => true,
                'overtime_after_minutes' => 0,
                'gps_required' => true, 'selfie_required' => true, 'status' => 'active',
                'notes' => 'Shift pengujian Owner QA. Bukti selfie/GPS aktif, jam fleksibel.',
            ];
            // Keeps the provisioner compatible with a server that has not yet
            // received the later overtime-max migration.
            if (Schema::hasColumn('attendance_shifts', 'overtime_max_minutes')) {
                $shiftAttributes['overtime_max_minutes'] = 180;
            }
            $shift = AttendanceShift::query()->withTrashed()->firstOrCreate(
                ['company_id' => $company->id, 'branch_id' => null, 'code' => 'OWNER-QA'],
                $shiftAttributes
            );
            $shift->restore();
            $shift->update(['status' => 'active']);
            $assignment = AttendanceShiftAssignment::query()->withTrashed()->firstOrNew(
                ['company_id' => $company->id, 'employee_id' => $employee->id, 'attendance_shift_id' => $shift->id]
            );
            $assignment->fill(['branch_id' => $branch?->id, 'start_date' => now()->toDateString(), 'end_date' => null, 'status' => 'active', 'notes' => 'Otomatis untuk Owner QA']);
            $assignment->deleted_at = null;
            $assignment->save();

            if (Schema::hasTable('attendance_location_policies')) {
                AttendanceLocationPolicy::query()->updateOrCreate(
                    ['company_id' => $company->id, 'scope_type' => 'division', 'scope_id' => $division->id],
                    ['name' => 'Mobile QA / Owner Test — Bebas Lokasi', 'mode' => 'anywhere', 'latitude' => null, 'longitude' => null, 'radius_meter' => null, 'is_active' => true, 'notes' => 'Khusus akun Owner QA; selfie dan GPS tetap disimpan.', 'updated_by' => $user->id]
                );
            }

            return compact('user', 'employee', 'division', 'shift');
        });
    }
}
