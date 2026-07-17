<?php

namespace Database\Seeders;

use App\Models\BusinessTrip;
use App\Models\BusinessTripPolicy;
use App\Models\Company;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\EmployeeKpiAssessment;
use App\Models\EmployeeKpiAssessmentItem;
use App\Models\EmployeeRequest;
use App\Models\KpiStandard;
use App\Models\OperationalVehicle;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Models\PayrollSlipItem;
use App\Models\VehicleExpense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder ini dibuat idempotent supaya bisa di-run ulang tanpa
        // menggandakan data demo. Tujuannya hanya mengisi halaman yang kosong.
        $company = Company::query()->where('code', 'OEMS')->first();
        if (! $company) {
            $this->command?->warn('Company OEMS belum ada, demo scenario dilewati.');
            return;
        }

        $branch = DB::table('branches')
            ->where('company_id', $company->id)
            ->where('code', 'HO')
            ->first();

        $divisions = DB::table('divisions')
            ->where('company_id', $company->id)
            ->pluck('id', 'code');

        $positions = DB::table('positions')
            ->where('company_id', $company->id)
            ->pluck('id', 'code');

        $contractTypes = ContractType::query()
            ->where('company_id', $company->id)
            ->pluck('id', 'template_key');

        $developer = DB::table('users')
            ->where('email', 'developer@oems.local')
            ->first();

        $owner = $this->upsertUser([
            'email' => 'owner@oems.local',
            'name' => 'Owner OSM',
            'username' => 'owner',
        ], $company->id);

        $hr = $this->upsertUser([
            'email' => 'hr@oems.local',
            'name' => 'HR OSM',
            'username' => 'hr',
        ], $company->id);

        $employeePermanent = $this->upsertEmployee([
            'employee_no' => 'OSM-EMP-001',
            'name' => 'Andi Pratama',
            'nickname' => 'Andi',
            'email' => 'andi.pratama@oems.local',
            'phone' => '081200000001',
            'join_date' => Carbon::parse('2026-01-05'),
            'employment_status' => 'permanent',
            'work_status' => 'active',
            'branch_id' => $branch?->id,
            'division_id' => $divisions['MANAGEMENT'] ?? null,
            'position_id' => $positions['GENERAL_MANAGER'] ?? null,
            'basic_salary' => 12000000,
            'fixed_allowance' => 1500000,
            'meal_allowance' => 500000,
            'transport_allowance' => 750000,
            'position_allowance' => 2000000,
            'kpi_incentive_max' => 2500000,
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Andi Pratama',
            'is_kpi_enabled' => true,
            'is_attendance_required' => true,
        ], $company->id);

        $employeeContract = $this->upsertEmployee([
            'employee_no' => 'OSM-EMP-002',
            'name' => 'Budi Saputra',
            'nickname' => 'Budi',
            'email' => 'budi.saputra@oems.local',
            'phone' => '081200000002',
            'join_date' => Carbon::parse('2026-04-01'),
            'employment_status' => 'contract',
            'work_status' => 'active',
            'branch_id' => $branch?->id,
            'division_id' => $divisions['TECH'] ?? null,
            'position_id' => $positions['TECHNICIAN'] ?? null,
            'basic_salary' => 6500000,
            'fixed_allowance' => 800000,
            'meal_allowance' => 300000,
            'transport_allowance' => 400000,
            'position_allowance' => 500000,
            'kpi_incentive_max' => 1500000,
            'bank_name' => 'BRI',
            'bank_account_no' => '9876543210',
            'bank_account_name' => 'Budi Saputra',
            'is_kpi_enabled' => true,
            'is_attendance_required' => true,
        ], $company->id);

        $employeeProbation = $this->upsertEmployee([
            'employee_no' => 'OSM-EMP-003',
            'name' => 'Citra Lestari',
            'nickname' => 'Citra',
            'email' => 'citra.lestari@oems.local',
            'phone' => '081200000003',
            'join_date' => Carbon::parse('2026-07-01'),
            'employment_status' => 'probation',
            'work_status' => 'active',
            'branch_id' => $branch?->id,
            'division_id' => $divisions['HR'] ?? null,
            'position_id' => $positions['HR_STAFF'] ?? null,
            'basic_salary' => 5000000,
            'fixed_allowance' => 500000,
            'meal_allowance' => 250000,
            'transport_allowance' => 250000,
            'position_allowance' => 250000,
            'kpi_incentive_max' => 1000000,
            'bank_name' => 'Mandiri',
            'bank_account_no' => '111222333',
            'bank_account_name' => 'Citra Lestari',
            'is_kpi_enabled' => true,
            'is_attendance_required' => true,
        ], $company->id);

        $this->upsertContract($company->id, $employeeProbation, $contractTypes['probation'] ?? null, [
            'contract_no' => 'CTR-OSM-2026-0001',
            'start_date' => Carbon::parse('2026-07-01'),
            'end_date' => Carbon::parse('2026-10-01'),
            'duration_month' => 3,
            'status' => 'active',
            'basic_salary' => 5000000,
            'meal_allowance' => 250000,
            'transport_allowance' => 250000,
            'position_allowance' => 250000,
            'fixed_allowance' => 500000,
            'notes' => 'Dummy kontrak probation untuk demo end-to-end.',
            'settings' => ['template_key' => 'probation'],
        ], $developer?->id ?? null, $hr->id ?? null);

        $this->upsertContract($company->id, $employeeContract, $contractTypes['pkwt_1'] ?? null, [
            'contract_no' => 'CTR-OSM-2026-0002',
            'start_date' => Carbon::parse('2026-04-01'),
            'end_date' => Carbon::parse('2027-03-31'),
            'duration_month' => 12,
            'status' => 'active',
            'basic_salary' => 6500000,
            'meal_allowance' => 300000,
            'transport_allowance' => 400000,
            'position_allowance' => 500000,
            'fixed_allowance' => 800000,
            'notes' => 'Dummy kontrak PKWT tahap 1 untuk teknisi lapangan.',
            'settings' => ['template_key' => 'pkwt_1'],
        ], $developer?->id ?? null, $owner->id);

        $this->upsertContract($company->id, $employeePermanent, $contractTypes['pkwt_2'] ?? null, [
            'contract_no' => 'CTR-OSM-2026-0003',
            'start_date' => Carbon::parse('2026-01-05'),
            'end_date' => Carbon::parse('2027-01-04'),
            'duration_month' => 12,
            'status' => 'approved',
            'basic_salary' => 12000000,
            'meal_allowance' => 500000,
            'transport_allowance' => 750000,
            'position_allowance' => 2000000,
            'fixed_allowance' => 1500000,
            'notes' => 'Dummy kontrak PKWT tahap 2 / perpanjangan untuk demo approval.',
            'settings' => ['template_key' => 'pkwt_2'],
        ], $developer?->id ?? null, $owner->id);

        $this->upsertContract($company->id, $employeeProbation, $contractTypes['internship'] ?? null, [
            'contract_no' => 'CTR-OSM-2026-0004',
            'start_date' => Carbon::parse('2026-07-01'),
            'end_date' => Carbon::parse('2026-12-31'),
            'duration_month' => 6,
            'status' => 'draft',
            'basic_salary' => 0,
            'meal_allowance' => 0,
            'transport_allowance' => 0,
            'position_allowance' => 0,
            'fixed_allowance' => 0,
            'notes' => 'Dummy dokumen pemagangan untuk demo template.',
            'settings' => ['template_key' => 'internship'],
        ], $developer?->id ?? null, $hr->id ?? null);

        $this->upsertRequests($company->id, $branch?->id, $employeeContract, $owner->id, $hr->id);
        $this->upsertPayroll($company->id, $branch?->id, $employeePermanent, $employeeContract, $employeeProbation, $owner->id, $hr->id);
        $this->upsertKpiAssessments($company->id, $employeePermanent, $employeeContract, $employeeProbation, $owner->id, $hr->id);
        $this->upsertOperations($company->id, $branch?->id, $employeeContract, $employeePermanent, $owner->id, $hr->id);

        $this->command?->info('Demo scenario seeder selesai.');
    }

    private function upsertUser(array $data, int $companyId): object
    {
        $payload = array_merge($data, [
            'company_id' => $companyId,
            'password' => bcrypt('12345678'),
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
            'email_verified_at' => now(),
            'last_activity_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->updateOrInsert(
            ['email' => $data['email']],
            $payload
        );

        return DB::table('users')->where('email', $data['email'])->first();
    }

    private function upsertEmployee(array $data, int $companyId): Employee
    {
        $payload = array_merge($data, [
            'company_id' => $companyId,
            'employee_no' => $data['employee_no'],
            'name' => $data['name'],
            'work_status' => $data['work_status'] ?? 'active',
            'is_kpi_enabled' => $data['is_kpi_enabled'] ?? true,
            'is_attendance_required' => $data['is_attendance_required'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee = Employee::query()->updateOrCreate(
            ['company_id' => $companyId, 'employee_no' => $data['employee_no']],
            $payload
        );

        return $employee;
    }

    private function upsertContract(int $companyId, Employee $employee, ?int $contractTypeId, array $data, ?int $createdBy, ?int $approvedBy): void
    {
        if (! $contractTypeId) {
            return;
        }

        $payload = array_merge($data, [
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'contract_type_id' => $contractTypeId,
            'employee_no' => $employee->employee_no,
            'employee_name' => $employee->name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'branch_name' => $employee->branch?->name,
            'division_name' => $employee->division?->name,
            'position_name' => $employee->position?->name,
            'created_by' => $createdBy,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'is_latest' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        EmployeeContract::query()->updateOrCreate(
            ['company_id' => $companyId, 'contract_no' => $data['contract_no']],
            $payload
        );
    }

    private function upsertRequests(int $companyId, ?int $branchId, Employee $employee, ?int $ownerId, ?int $hrId): void
    {
        $items = [
            [
                'request_no' => 'REQ-OSM-2026-0001',
                'type' => 'leave',
                'reason' => 'Cuti tahunan demo untuk kebutuhan uji alur approval.',
                'start_date' => Carbon::parse('2026-07-20'),
                'end_date' => Carbon::parse('2026-07-22'),
                'total_days' => 3,
                'status' => 'submitted',
                'submitted_at' => now(),
            ],
            [
                'request_no' => 'REQ-OSM-2026-0002',
                'type' => 'sick',
                'reason' => 'Izin sakit demo dengan lampiran internal.',
                'start_date' => Carbon::parse('2026-07-18'),
                'end_date' => Carbon::parse('2026-07-18'),
                'total_days' => 1,
                'status' => 'approved',
                'submitted_at' => now()->subDay(),
                'approved_by' => $hrId,
                'approved_at' => now()->subDay(),
                'hr_note' => 'Disetujui untuk demo.',
            ],
            [
                'request_no' => 'REQ-OSM-2026-0003',
                'type' => 'cash_advance',
                'reason' => 'Kasbon demo untuk kebutuhan operasional lapangan.',
                'requested_amount' => 1500000,
                'approved_amount' => 1200000,
                'installment_count' => 3,
                'status' => 'approved',
                'submitted_at' => now()->subDays(2),
                'approved_by' => $hrId,
                'approved_at' => now()->subDays(2),
                'hr_note' => 'Disetujui sebagian agar demo cicilan terlihat.',
            ],
            [
                'request_no' => 'REQ-OSM-2026-0004',
                'type' => 'receivable',
                'reason' => 'Piutang demo yang dicicil lewat payroll.',
                'requested_amount' => 2400000,
                'approved_amount' => 2400000,
                'installment_count' => 6,
                'status' => 'approved',
                'submitted_at' => now()->subDays(3),
                'approved_by' => $ownerId,
                'approved_at' => now()->subDays(3),
                'hr_note' => 'Approved owner untuk demo piutang.',
            ],
            [
                'request_no' => 'REQ-OSM-2026-0005',
                'type' => 'overtime',
                'reason' => 'Lembur demo untuk penutupan laporan mingguan.',
                'start_date' => Carbon::parse('2026-07-15'),
                'end_date' => Carbon::parse('2026-07-15'),
                'total_days' => 1,
                'status' => 'submitted',
                'submitted_at' => now()->subHours(5),
            ],
        ];

        foreach ($items as $item) {
            EmployeeRequest::query()->updateOrCreate(
                ['company_id' => $companyId, 'request_no' => $item['request_no']],
                array_merge($item, [
                    'branch_id' => $branchId,
                    'employee_id' => $employee->id,
                    'hr_note' => $item['hr_note'] ?? null,
                    'metadata' => [
                        'scenario' => 'demo',
                        'source' => 'DemoScenarioSeeder',
                    ],
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }

    private function upsertPayroll(int $companyId, ?int $branchId, Employee $employeePermanent, Employee $employeeContract, Employee $employeeProbation, ?int $ownerId, ?int $hrId): void
    {
        $period = PayrollPeriod::query()->updateOrCreate(
            ['company_id' => $companyId, 'period_year' => 2026, 'period_month' => 7],
            [
                'cutoff_date' => Carbon::parse('2026-07-31'),
                'salary_payment_date' => Carbon::parse('2026-07-31'),
                'kpi_payment_date' => Carbon::parse('2026-08-15'),
                'status' => 'approved',
                'total_gross' => 0,
                'total_deduction' => 0,
                'total_net' => 0,
                'total_kpi_bonus' => 0,
                'approved_by' => $hrId,
                'approved_at' => now(),
                'published_by' => $ownerId,
                'published_at' => now(),
                'settings_snapshot' => [
                    'demo' => true,
                    'salary_payment_day' => 31,
                    'kpi_payment_day' => 15,
                ],
            ]
        );

        $slips = [
            [$employeePermanent, 12000000, 1500000, 500000, 750000, 2000000, 1800000, 350000, 2500000],
            [$employeeContract, 6500000, 800000, 300000, 400000, 500000, 1250000, 150000, 1500000],
            [$employeeProbation, 5000000, 500000, 250000, 250000, 250000, 900000, 100000, 1000000],
        ];

        $totalGross = 0;
        $totalDeduction = 0;
        $totalNet = 0;
        $totalKpi = 0;

        foreach ($slips as $index => [$employee, $basic, $fixed, $meal, $transport, $position, $other, $attendanceDeduction, $kpiBonus]) {
            $gross = $basic + $fixed + $meal + $transport + $position + $other;
            $totalDeductionRow = $attendanceDeduction;
            $net = max(0, $gross - $totalDeductionRow);

            $slip = PayrollSlip::query()->updateOrCreate(
                ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'slip_no' => sprintf('SLP-OSM-2026-07-%03d', $index + 1),
                    'employee_no_snapshot' => $employee->employee_no,
                    'employee_name_snapshot' => $employee->name,
                    'branch_name_snapshot' => $employee->branch?->name,
                    'position_name_snapshot' => $employee->position?->name,
                    'bank_name_snapshot' => $employee->bank_name,
                    'bank_account_snapshot' => $employee->bank_account_no,
                    'basic_salary' => $basic,
                    'fixed_allowance' => $fixed,
                    'meal_allowance' => $meal,
                    'transport_allowance' => $transport,
                    'position_allowance' => $position,
                    'other_income' => $other,
                    'gross_income' => $gross,
                    'attendance_deduction' => $attendanceDeduction,
                    'receivable_deduction' => 0,
                    'other_deduction' => 0,
                    'total_deduction' => $totalDeductionRow,
                    'net_pay' => $net,
                    'kpi_bonus' => $kpiBonus,
                    'kpi_payment_date' => Carbon::parse('2026-08-15'),
                    'status' => 'approved',
                    'approved_by' => $hrId,
                    'approved_at' => now(),
                    'published_at' => now(),
                    'calculation_snapshot' => [
                        'demo' => true,
                        'period' => '07/2026',
                    ],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            PayrollSlipItem::query()->updateOrCreate(
                ['payroll_slip_id' => $slip->id, 'code' => 'BASIC'],
                [
                    'category' => 'income',
                    'name' => 'Gaji Pokok',
                    'amount' => $basic,
                    'is_taxable' => true,
                    'metadata' => ['demo' => true],
                ]
            );

            PayrollSlipItem::query()->updateOrCreate(
                ['payroll_slip_id' => $slip->id, 'code' => 'KPI'],
                [
                    'category' => 'income',
                    'name' => 'Bonus KPI',
                    'amount' => $kpiBonus,
                    'is_taxable' => false,
                    'metadata' => ['demo' => true],
                ]
            );

            $totalGross += $gross;
            $totalDeduction += $totalDeductionRow;
            $totalNet += $net;
            $totalKpi += $kpiBonus;
        }

        $period->update([
            'total_gross' => $totalGross,
            'total_deduction' => $totalDeduction,
            'total_net' => $totalNet,
            'total_kpi_bonus' => $totalKpi,
        ]);
    }

    private function upsertKpiAssessments(int $companyId, Employee $employeePermanent, Employee $employeeContract, Employee $employeeProbation, ?int $ownerId, ?int $hrId): void
    {
        $standards = KpiStandard::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with('items.aspect')
            ->get();

        $employees = [
            [$employeePermanent, 92.5, 'A', 2300000],
            [$employeeContract, 88.0, 'B+', 1400000],
            [$employeeProbation, 84.5, 'B', 900000],
        ];

        foreach ($employees as [$employee, $score, $grade, $bonus]) {
            $assessment = EmployeeKpiAssessment::query()->updateOrCreate(
                ['company_id' => $companyId, 'employee_id' => $employee->id, 'period_year' => 2026, 'period_month' => 7],
                [
                    'position_id' => $employee->position_id,
                    'kpi_standard_id' => $standards->first()?->id,
                    'assessor_id' => $hrId,
                    'approved_by' => $ownerId,
                    'total_score' => $score,
                    'grade' => $grade,
                    'bonus_maximum' => $employee->kpi_incentive_max,
                    'bonus_amount' => $bonus,
                    'payout_date' => Carbon::parse('2026-08-15'),
                    'status' => 'approved',
                    'submitted_at' => now()->subDay(),
                    'approved_at' => now(),
                    'source_summary' => [
                        'demo' => true,
                        'ticket' => 18,
                        'task' => 22,
                    ],
                    'notes' => 'Demo penilaian KPI untuk rangkaian uji end-to-end.',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            if ($standards->isNotEmpty()) {
                foreach ($standards->first()->items as $item) {
                    EmployeeKpiAssessmentItem::query()->updateOrCreate(
                        // Tabel item assessment menyimpan referensi ke aspek KPI,
                        // bukan ke baris kpi_standard_items langsung.
                        ['employee_kpi_assessment_id' => $assessment->id, 'kpi_aspect_id' => $item->kpi_aspect_id],
                        [
                            'kpi_aspect_id' => $item->kpi_aspect_id,
                            'aspect_name' => $item->aspect_name,
                            'guideline' => $item->guideline,
                            'score' => match ($item->aspect?->code) {
                                'DISCIPLINE' => 93,
                                'PRODUCTIVITY' => 91,
                                'QUALITY' => 90,
                                'SOP_ADMIN' => 88,
                                default => 86,
                            },
                            'weight' => $item->weight,
                            'weighted_score' => round(((float) match ($item->aspect?->code) {
                                'DISCIPLINE' => 93,
                                'PRODUCTIVITY' => 91,
                                'QUALITY' => 90,
                                'SOP_ADMIN' => 88,
                                default => 86,
                            }) * ((float) $item->weight) / 100, 2),
                            'source_type' => 'demo',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function upsertOperations(int $companyId, ?int $branchId, Employee $employeeContract, Employee $employeePermanent, ?int $ownerId, ?int $hrId): void
    {
        $policy = BusinessTripPolicy::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'daily_allowance' => 50000,
                'default_monthly_advance' => 1500000,
                'hr_delegation_limit' => 1000000,
                'delegation_valid_until' => Carbon::parse('2026-12-31'),
                'owner_approval_required' => true,
                'transport_paid_by_company' => true,
                'settings' => [
                    'demo' => true,
                ],
            ]
        );

        BusinessTrip::query()->updateOrCreate(
            ['company_id' => $companyId, 'trip_no' => 'TRP-OSM-2026-0001'],
            [
                'branch_id' => $branchId,
                'employee_id' => $employeeContract->id,
                'origin' => 'Bogor',
                'destination' => 'Jakarta',
                'purpose' => 'Demo survey jaringan dan meeting vendor.',
                'start_date' => Carbon::parse('2026-07-18'),
                'end_date' => Carbon::parse('2026-07-20'),
                'daily_allowance' => 50000,
                'transport_budget' => 350000,
                'lodging_budget' => 900000,
                'other_budget' => 150000,
                'advance_amount' => 1500000,
                'status' => 'hr_approved',
                'delegation_used' => true,
                'hr_approved_at' => now()->subDay(),
                'owner_approved_at' => null,
                'policy_snapshot' => [
                    'daily_allowance' => (string) $policy->daily_allowance,
                    'owner_approval_required' => $policy->owner_approval_required,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $vehicle = OperationalVehicle::query()->updateOrCreate(
            ['company_id' => $companyId, 'code' => 'MTR-OSM-001'],
            [
                'branch_id' => $branchId,
                'employee_id' => $employeePermanent->id,
                'ownership_type' => 'employee_private',
                'plate_no' => 'B 1234 OSM',
                'monthly_operational_allowance' => 1500000,
                'monthly_fuel_budget' => 1500000,
                'service_interval_months' => 1,
                'last_service_date' => Carbon::parse('2026-06-25'),
                'next_service_date' => Carbon::parse('2026-07-25'),
                'is_active' => true,
                'settings' => ['demo' => true],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        VehicleExpense::query()->updateOrCreate(
            ['company_id' => $companyId, 'operational_vehicle_id' => $vehicle->id, 'period_year' => 2026, 'period_month' => 7, 'type' => 'fuel'],
            [
                'planned_amount' => 1500000,
                'planned_payment_date' => Carbon::parse('2026-07-01'),
                'status' => 'planned',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        VehicleExpense::query()->updateOrCreate(
            ['company_id' => $companyId, 'operational_vehicle_id' => $vehicle->id, 'period_year' => 2026, 'period_month' => 8, 'type' => 'service'],
            [
                'planned_amount' => 500000,
                'planned_payment_date' => Carbon::parse('2026-08-01'),
                'status' => 'planned',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
