<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Setting\RolePermissionController;
use App\Http\Controllers\Setting\UserAccessController;
use App\Http\Controllers\Master\EmployeeController;
use App\Http\Controllers\HR\EmployeeContractController;
use App\Http\Controllers\HR\KpiController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
  Route::get('/login', [LoginController::class, 'index'])
    ->name('login');

  Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.proses');

  Route::get('/auth/google', [LoginController::class, 'redirectToGoogle'])
    ->name('google.login');

  Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])
    ->name('google.callback');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
  Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

  Route::get('/select-company', [LoginController::class, 'selectCompany'])
    ->name('company.select');

  Route::post('/select-company', [LoginController::class, 'setCompany'])
    ->name('company.set');
});

Route::middleware([
    'auth',
    'company.selected',
    'permission.company.context',
])->group(function () {

    Route::resource('employees', EmployeeController::class)
    ->names('employees');

    /* Dokumen identitas pegawai disimpan privat dan tidak memakai storage URL. */
    Route::get('/employees/{employee}/documents', [\App\Http\Controllers\Master\EmployeeDocumentController::class, 'index'])
      ->middleware('permission:employee-document.view')->name('employees.documents.index');
    Route::post('/employees/{employee}/documents', [\App\Http\Controllers\Master\EmployeeDocumentController::class, 'store'])
      ->middleware('permission:employee-document.manage')->name('employees.documents.store');
    Route::put('/employees/{employee}/documents/{document}/status', [\App\Http\Controllers\Master\EmployeeDocumentController::class, 'updateStatus'])
      ->middleware('permission:employee-document.manage')->name('employees.documents.status');
    Route::get('/employees/{employee}/documents/{document}/download', [\App\Http\Controllers\Master\EmployeeDocumentController::class, 'download'])
      ->middleware('permission:employee-document.view')->name('employees.documents.download');
    Route::delete('/employees/{employee}/documents/{document}', [\App\Http\Controllers\Master\EmployeeDocumentController::class, 'destroy'])
      ->middleware('permission:employee-document.manage')->name('employees.documents.destroy');

});

/*
|--------------------------------------------------------------------------
| Internal Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
  'auth',
  'company.selected',
  'permission.company.context',
])->group(function () {
  Route::get('/', function () {
    return redirect()->route('dashboard');
  });

  Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('permission:dashboard.view')
    ->name('dashboard');

  // Lonceng mengambang pengajuan dari APK OvallHR. Semua endpoint tetap
  // berada di session company AppOEMS, bukan API publik/mobile.
  Route::get('/notifications/ovallhr', [\App\Http\Controllers\System\OvallHrNotificationController::class, 'index'])
    ->middleware('permission:hr-request.view')
    ->name('notifications.ovallhr.index');
  Route::get('/notifications/ovallhr/{notification}/open', [\App\Http\Controllers\System\OvallHrNotificationController::class, 'open'])
    ->middleware('permission:hr-request.view')
    ->name('notifications.ovallhr.open');

  /*
  |--------------------------------------------------------------------------
  | OvallHR Control Center
  |--------------------------------------------------------------------------
  | Satu pintu operasional untuk seluruh data yang dibaca atau dikendalikan
  | oleh APK OvallHR. Route modul asal tetap dipertahankan demi kompatibilitas
  | bookmark lama, tetapi sidebar hanya perlu menampilkan pusat ini.
  */
  Route::get('/ovallhr-control', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'index'])
    ->middleware('permission:attendance.view')
    ->name('ovallhr.control-center.index');
  Route::post('/ovallhr-control/announcements', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'storeAnnouncement'])
    ->middleware('permission:mobile-release.manage')
    ->name('ovallhr.control-center.announcements.store');
  Route::post('/ovallhr-control/announcements/{announcement}/toggle', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'toggleAnnouncement'])
    ->middleware('permission:mobile-release.manage')
    ->name('ovallhr.control-center.announcements.toggle');
  Route::put('/ovallhr-control/branding', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'updateBranding'])
    ->middleware('permission:mobile-release.manage')
    ->name('ovallhr.control-center.branding.update');
  Route::get('/ovallhr-control/preview', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'preview'])
    ->middleware('permission:mobile-release.view')
    ->name('ovallhr.control-center.preview');
  Route::get('/ovallhr-control/work-tracking', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'workTracking'])
    ->middleware('permission:attendance.view')
    ->name('ovallhr.control-center.work-tracking');
  Route::put('/ovallhr-control/birthday', [\App\Http\Controllers\OvallHr\OvallHrControlCenterController::class, 'updateBirthdaySettings'])
    ->middleware('permission:mobile-release.manage')
    ->name('ovallhr.control-center.birthday.update');

  Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/user-access', [UserAccessController::class, 'index'])
      ->middleware('permission:users.view')
      ->name('user-access.index');

    Route::get('/mobile-releases', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'index'])
      ->middleware('permission:mobile-release.view')->name('mobile-releases.index');
    Route::post('/mobile-releases', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'storeRelease'])
      ->middleware('permission:mobile-release.manage')->name('mobile-releases.store');
    Route::post('/mobile-releases/{release}/publish', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'publish'])
      ->middleware('permission:mobile-release.manage')->name('mobile-releases.publish');
    Route::post('/mobile-features', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'saveFeature'])
      ->middleware('permission:mobile-release.manage')->name('mobile-features.store');
    Route::post('/mobile-features/{feature}/toggle', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'toggleFeature'])
      ->middleware('permission:mobile-release.manage')->name('mobile-features.toggle');
    Route::post('/mobile-features/{key}/toggle-menu', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'toggleKnownFeature'])
      ->middleware('permission:mobile-release.manage')->name('mobile-features.toggle-known');
    Route::put('/mobile-features/{key}/menu', [\App\Http\Controllers\Setting\MobileReleaseCenterController::class, 'updateKnownFeature'])
      ->middleware('permission:mobile-release.manage')->name('mobile-features.update-known');

    Route::get('/user-access/data', [UserAccessController::class, 'data'])
      ->middleware('permission:users.view')
      ->name('user-access.data');

    Route::get('/user-access/{user}/edit', [UserAccessController::class, 'edit'])
      ->middleware('permission:users.update')
      ->name('user-access.edit');

    Route::put('/user-access/{user}', [UserAccessController::class, 'update'])
      ->middleware('permission:users.update')
      ->name('user-access.update');

    Route::post('/user-access/{user}/assign-role', [UserAccessController::class, 'assignRole'])
      ->middleware('permission:users.update')
      ->name('user-access.assign-role');

    Route::post('/user-access/{user}/assign-permission', [UserAccessController::class, 'assignPermission'])
      ->middleware('permission:permissions.update')
      ->name('user-access.assign-permission');
  });

  Route::prefix('settings')->name('settings.')->group(function () {
    // User Access existing routes...

    Route::get('/role-permission', [RolePermissionController::class, 'index'])
      ->middleware('permission:roles.view')
      ->name('role-permission.index');

    Route::get('/role-permission/data', [RolePermissionController::class, 'data'])
      ->middleware('permission:roles.view')
      ->name('role-permission.data');

    Route::post('/role-permission', [RolePermissionController::class, 'store'])
      ->middleware('permission:roles.create')
      ->name('role-permission.store');

    Route::get('/role-permission/{role}/edit', [RolePermissionController::class, 'edit'])
      ->middleware('permission:roles.update')
      ->name('role-permission.edit');

    Route::put('/role-permission/{role}', [RolePermissionController::class, 'update'])
      ->middleware('permission:roles.update')
      ->name('role-permission.update');

    Route::delete('/role-permission/{role}', [RolePermissionController::class, 'destroy'])
      ->middleware('permission:roles.delete')
      ->name('role-permission.destroy');

    Route::get('/integrations', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'index'])
      ->middleware('permission:integration.view')
      ->name('integrations.index');
    Route::put('/integrations/{connection}', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'update'])
      ->middleware('permission:integration.manage')
      ->name('integrations.update');
    Route::post('/integrations/{connection}/credentials', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'generateCredentials'])
      ->middleware('permission:integration.manage')
      ->name('integrations.credentials');
    Route::post('/integrations/{connection}/credentials/reveal', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'revealCredentials'])
      ->middleware('permission:integration.manage')
      ->name('integrations.credentials.reveal');
    Route::post('/integrations/{connection}/test-live-direct', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'testLiveDirect'])
      ->middleware('permission:integration.manage')
      ->name('integrations.test-live-direct');
    Route::post('/integrations/test', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'queueTest'])
      ->middleware('permission:integration.dispatch')
      ->name('integrations.test');
    Route::post('/integrations/dispatch', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'dispatch'])
      ->middleware('permission:integration.dispatch')
      ->name('integrations.dispatch');
    Route::post('/integrations/outbox/{event}/retry', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'retry'])
      ->middleware('permission:integration.dispatch')
      ->name('integrations.retry');
    Route::post('/integrations/health', [\App\Http\Controllers\Setting\IntegrationCenterController::class, 'refreshHealth'])
      ->middleware('permission:health.view')
      ->name('integrations.health');

    /* Tarif BPJS merupakan master payroll; controller hanya mengarahkan ke service. */
    Route::get('/bpjs-calculation', [\App\Http\Controllers\Setting\BpjsCalculationController::class, 'index'])
      ->middleware('permission:bpjs-calculation.view')
      ->name('bpjs-calculation.index');
    Route::put('/bpjs-calculation', [\App\Http\Controllers\Setting\BpjsCalculationController::class, 'update'])
      ->middleware('permission:bpjs-calculation.manage')
      ->name('bpjs-calculation.update');
    Route::post('/bpjs-calculation/preview', [\App\Http\Controllers\Setting\BpjsCalculationController::class, 'preview'])
      ->middleware('permission:bpjs-calculation.view')
      ->name('bpjs-calculation.preview');
  });

  /*
|--------------------------------------------------------------------------
| Master Branch
|--------------------------------------------------------------------------
*/

Route::prefix('master')->name('master.')->group(function () {

    Route::get('/branches', [\App\Http\Controllers\Master\BranchController::class, 'index'])
        ->middleware('permission:branch.view')
        ->name('branches.index');

    Route::get('/branches/data', [\App\Http\Controllers\Master\BranchController::class, 'data'])
        ->middleware('permission:branch.view')
        ->name('branches.data');

    Route::get('/branches/create', [\App\Http\Controllers\Master\BranchController::class, 'create'])
        ->middleware('permission:branch.create')
        ->name('branches.create');

    Route::post('/branches', [\App\Http\Controllers\Master\BranchController::class, 'store'])
        ->middleware('permission:branch.create')
        ->name('branches.store');

    Route::get('/branches/{branch}/edit', [\App\Http\Controllers\Master\BranchController::class, 'edit'])
        ->middleware('permission:branch.update')
        ->name('branches.edit');

    Route::put('/branches/{branch}', [\App\Http\Controllers\Master\BranchController::class, 'update'])
        ->middleware('permission:branch.update')
        ->name('branches.update');

    Route::delete('/branches/{branch}', [\App\Http\Controllers\Master\BranchController::class, 'destroy'])
        ->middleware('permission:branch.delete')
        ->name('branches.destroy');

        /*
|--------------------------------------------------------------------------
| Master Division
|--------------------------------------------------------------------------
*/

Route::get('/divisions', [\App\Http\Controllers\Master\DivisionController::class, 'index'])
    ->middleware('permission:division.view')
    ->name('divisions.index');

Route::get('/divisions/create', [\App\Http\Controllers\Master\DivisionController::class, 'create'])
    ->middleware('permission:division.create')
    ->name('divisions.create');

Route::post('/divisions', [\App\Http\Controllers\Master\DivisionController::class, 'store'])
    ->middleware('permission:division.create')
    ->name('divisions.store');

Route::get('/divisions/{division}/edit', [\App\Http\Controllers\Master\DivisionController::class, 'edit'])
    ->middleware('permission:division.update')
    ->name('divisions.edit');

Route::put('/divisions/{division}', [\App\Http\Controllers\Master\DivisionController::class, 'update'])
    ->middleware('permission:division.update')
    ->name('divisions.update');

Route::delete('/divisions/{division}', [\App\Http\Controllers\Master\DivisionController::class, 'destroy'])
    ->middleware('permission:division.delete')
    ->name('divisions.destroy');

Route::get('/positions', [\App\Http\Controllers\Master\PositionController::class, 'index'])
    ->middleware('permission:position.view')->name('positions.index');
Route::post('/positions', [\App\Http\Controllers\Master\PositionController::class, 'store'])
    ->middleware('permission:position.create')->name('positions.store');
Route::put('/positions/{position}', [\App\Http\Controllers\Master\PositionController::class, 'update'])
    ->middleware('permission:position.update')->name('positions.update');
Route::delete('/positions/{position}', [\App\Http\Controllers\Master\PositionController::class, 'destroy'])
    ->middleware('permission:position.delete')->name('positions.destroy');
 
  

   /*
|--------------------------------------------------------------------------
| Master Contract Type
|--------------------------------------------------------------------------
*/

Route::get('/contract-types', [\App\Http\Controllers\Master\ContractTypeController::class, 'index'])
    ->middleware('permission:contract-type.view')
    ->name('contract-types.index');

Route::get('/contract-types/create', [\App\Http\Controllers\Master\ContractTypeController::class, 'create'])
    ->middleware('permission:contract-type.create')
    ->name('contract-types.create');

Route::post('/contract-types', [\App\Http\Controllers\Master\ContractTypeController::class, 'store'])
    ->middleware('permission:contract-type.create')
    ->name('contract-types.store');

Route::get('/contract-types/{contractType}/edit', [\App\Http\Controllers\Master\ContractTypeController::class, 'edit'])
    ->middleware('permission:contract-type.update')
    ->name('contract-types.edit');

Route::put('/contract-types/{contractType}', [\App\Http\Controllers\Master\ContractTypeController::class, 'update'])
    ->middleware('permission:contract-type.update')
    ->name('contract-types.update');

Route::delete('/contract-types/{contractType}', [\App\Http\Controllers\Master\ContractTypeController::class, 'destroy'])
    ->middleware('permission:contract-type.delete')
    ->name('contract-types.destroy');

/*
|--------------------------------------------------------------------------
| Master Dokumen Perusahaan
|--------------------------------------------------------------------------
| Template surat/SOP dipisahkan dari kontrak pegawai. Kontrak tetap dikelola
| pada Master Kontrak agar perubahan pasal tidak mengubah dokumen pegawai.
*/
Route::get('/company-documents', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'index'])
    ->middleware('permission:company-document.view')
    ->name('company-documents.index');
Route::get('/company-documents/create', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'create'])
    ->middleware('permission:company-document.create')
    ->name('company-documents.create');
Route::post('/company-documents', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'store'])
    ->middleware('permission:company-document.create')
    ->name('company-documents.store');
Route::get('/company-documents/{companyDocument}', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'show'])
    ->middleware('permission:company-document.view')
    ->name('company-documents.show');
Route::get('/company-documents/{companyDocument}/edit', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'edit'])
    ->middleware('permission:company-document.update')
    ->name('company-documents.edit');
Route::put('/company-documents/{companyDocument}', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'update'])
    ->middleware('permission:company-document.update')
    ->name('company-documents.update');
Route::delete('/company-documents/{companyDocument}', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'destroy'])
    ->middleware('permission:company-document.delete')
    ->name('company-documents.destroy');
Route::get('/company-documents/{companyDocument}/print', [\App\Http\Controllers\Master\CompanyDocumentController::class, 'print'])
    ->middleware('permission:company-document.view')
    ->name('company-documents.print');
    }); // <-- tutup master di sini

/*
|--------------------------------------------------------------------------
| BPJS Readiness Center
|--------------------------------------------------------------------------
| Internal readiness only. The official filing remains in BPJS official
| channels after HR verifies F1/F1a/F2 and the supporting documents.
*/
Route::prefix('hr/bpjs-readiness')->name('hr.bpjs-readiness.')->group(function () {
    Route::get('/', [\App\Http\Controllers\HR\BpjsReadinessController::class, 'index'])
        ->middleware('permission:bpjs-registration.view')->name('index');
    Route::put('/profile', [\App\Http\Controllers\HR\BpjsReadinessController::class, 'updateProfile'])
        ->middleware('permission:bpjs-registration.manage')->name('profile.update');
    Route::put('/employees/{employee}', [\App\Http\Controllers\HR\BpjsReadinessController::class, 'updateEmployee'])
        ->middleware('permission:bpjs-registration.manage')->name('employees.update');
    Route::get('/print', [\App\Http\Controllers\HR\BpjsReadinessController::class, 'print'])
        ->middleware('permission:bpjs-registration.view')->name('print');
});
/*
|--------------------------------------------------------------------------
| Attendance
|--------------------------------------------------------------------------
*/


Route::prefix('attendance')
    ->name('attendance.')
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\Attendance\AttendanceController::class, 'index'])
    ->middleware('permission:attendance.view')
    ->name('index');

        // Dashboard review HR. Bukti selfie dibuka melalui route terproteksi
        // agar hanya user berizin absensi yang dapat melihatnya dari AppOEMS.
        Route::get('/records/{attendance}/proof/{direction}', [\App\Http\Controllers\Attendance\AttendanceController::class, 'proof'])
            ->middleware('permission:attendance.view')
            ->name('records.proof');
        Route::post('/records/{attendance}/approve', [\App\Http\Controllers\Attendance\AttendanceController::class, 'approve'])
            ->middleware('permission:attendance.update')
            ->name('records.approve');
        Route::post('/records/{attendance}/reject', [\App\Http\Controllers\Attendance\AttendanceController::class, 'reject'])
            ->middleware('permission:attendance.update')
            ->name('records.reject');

        Route::get('/shifts', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'index'])
            ->middleware('permission:attendance.shift.view')
            ->name('shifts.index');

        Route::get('/shifts/create', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'create'])
            ->middleware('permission:attendance.shift.create')
            ->name('shifts.create');

        Route::post('/shifts', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'store'])
            ->middleware('permission:attendance.shift.create')
            ->name('shifts.store');

        Route::get('/shifts/{shift}/edit', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'edit'])
            ->middleware('permission:attendance.shift.update')
            ->name('shifts.edit');

        Route::put('/shifts/{shift}', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'update'])
            ->middleware('permission:attendance.shift.update')
            ->name('shifts.update');

        Route::delete('/shifts/{shift}', [\App\Http\Controllers\Attendance\AttendanceShiftController::class, 'destroy'])
            ->middleware('permission:attendance.shift.delete')
            ->name('shifts.destroy');
        
        /*
|--------------------------------------------------------------------------
| Jadwal Shift
|--------------------------------------------------------------------------
*/

Route::get('/shift-assignments', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'index'])
    ->middleware('permission:attendance.shift.assignment.view')
    ->name('shift-assignments.index');

Route::get('/shift-assignments/create', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'create'])
    ->middleware('permission:attendance.shift.assignment.create')
    ->name('shift-assignments.create');

Route::post('/shift-assignments', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'store'])
    ->middleware('permission:attendance.shift.assignment.create')
    ->name('shift-assignments.store');

Route::get('/shift-assignments/{assignment}/edit', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'edit'])
    ->middleware('permission:attendance.shift.assignment.update')
    ->name('shift-assignments.edit');

Route::put('/shift-assignments/{assignment}', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'update'])
    ->middleware('permission:attendance.shift.assignment.update')
    ->name('shift-assignments.update');

Route::delete('/shift-assignments/{assignment}', [\App\Http\Controllers\Attendance\AttendanceShiftAssignmentController::class, 'destroy'])
    ->middleware('permission:attendance.shift.assignment.delete')
    ->name('shift-assignments.destroy');

    });
  /*
|--------------------------------------------------------------------------
| Human Resource
|--------------------------------------------------------------------------
*/

Route::prefix('hr')
    ->name('hr.')
    ->group(function () {

        Route::get('/settings', [\App\Http\Controllers\HR\HrSettingsController::class, 'index'])
            ->middleware('permission:attendance.view')->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\HR\HrSettingsController::class, 'update'])
            ->name('settings.update');
        // Developer-only source of truth for company/site/division geofence.
        Route::get('/attendance-locations', [\App\Http\Controllers\HR\AttendanceLocationPolicyController::class, 'index'])
            ->middleware('permission:attendance.view')->name('attendance-locations.index');
        Route::post('/attendance-locations', [\App\Http\Controllers\HR\AttendanceLocationPolicyController::class, 'store'])
            ->middleware('permission:attendance.view')->name('attendance-locations.store');
        Route::put('/attendance-locations/{policy}', [\App\Http\Controllers\HR\AttendanceLocationPolicyController::class, 'update'])
            ->middleware('permission:attendance.view')->name('attendance-locations.update');
        Route::delete('/attendance-locations/{policy}', [\App\Http\Controllers\HR\AttendanceLocationPolicyController::class, 'destroy'])
            ->middleware('permission:attendance.view')->name('attendance-locations.destroy');
        Route::get('/compensation', [\App\Http\Controllers\HR\CompensationController::class, 'index'])
            ->middleware('permission:payroll.view')->name('compensation.index');

        Route::prefix('kpi')->name('kpi.')->group(function () {
            Route::get('/', [KpiController::class, 'index'])
                ->middleware('permission:kpi.view')
                ->name('index');
            Route::get('/aspects', [KpiController::class, 'aspects'])
                ->middleware('permission:kpi.view')
                ->name('aspects');
            Route::post('/aspects', [KpiController::class, 'storeAspect'])
                ->middleware('permission:kpi.create')
                ->name('aspects.store');
            Route::get('/standards/create', [KpiController::class, 'createStandard'])
                ->middleware('permission:kpi.create')
                ->name('standards.create');
            Route::post('/standards', [KpiController::class, 'storeStandard'])
                ->middleware('permission:kpi.create')
                ->name('standards.store');
            Route::get('/assessments/create', [KpiController::class, 'createAssessment'])
                ->middleware('permission:kpi.create')
                ->name('assessments.create');
            Route::post('/assessments', [KpiController::class, 'storeAssessment'])
                ->middleware('permission:kpi.create')
                ->name('assessments.store');
            Route::get('/assessments/{assessment}', [KpiController::class, 'show'])
                ->middleware('permission:kpi.view')
                ->name('assessments.show');
            Route::post('/assessments/{assessment}/approve', [KpiController::class, 'approve'])
                ->middleware('permission:kpi.approve')
                ->name('assessments.approve');
            Route::post('/assessments/{assessment}/reject', [KpiController::class, 'reject'])
                ->middleware('permission:kpi.approve')
                ->name('assessments.reject');
        });

        Route::prefix('requests')->name('requests.')->group(function () {
            Route::get('/leave', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'index'])
                ->defaults('request_types', ['leave'])->middleware('permission:hr-request.view')->name('leave');
            Route::get('/permission-sick', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'index'])
                ->defaults('request_types', ['permission', 'sick'])->middleware('permission:hr-request.view')->name('permission-sick');
            Route::get('/overtime', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'index'])
                ->defaults('request_types', ['overtime'])->middleware('permission:hr-request.view')->name('overtime');
            Route::get('/finance', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'index'])
                ->defaults('request_types', ['cash_advance', 'receivable', 'reimbursement'])->middleware('permission:hr-request.view')->name('finance');
            Route::get('/', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'index'])
                ->middleware('permission:hr-request.view')->name('index');
            Route::post('/{employeeRequest}/approve', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'approve'])
                ->middleware('permission:hr-request.approve')->name('approve');
            Route::post('/{employeeRequest}/reject', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'reject'])
                ->middleware('permission:hr-request.approve')->name('reject');
            Route::put('/policies/{policy}', [\App\Http\Controllers\HR\EmployeeRequestController::class, 'updatePolicy'])
                ->middleware('permission:hr-request.policy')->name('policies.update');
        });

        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [\App\Http\Controllers\HR\PayrollController::class, 'index'])
                ->middleware('permission:payroll.view')->name('index');
            Route::post('/generate', [\App\Http\Controllers\HR\PayrollController::class, 'generate'])
                ->middleware('permission:payroll.create')->name('generate');
            Route::get('/periods/{period}', [\App\Http\Controllers\HR\PayrollController::class, 'show'])
                ->middleware('permission:payroll.view')->name('show');
            Route::post('/periods/{period}/approve', [\App\Http\Controllers\HR\PayrollController::class, 'approve'])
                ->middleware('permission:payroll.approve')->name('approve');
            Route::post('/periods/{period}/publish', [\App\Http\Controllers\HR\PayrollController::class, 'publish'])
                ->middleware('permission:payroll.publish')->name('publish');
            Route::get('/slips/{slip}/print', [\App\Http\Controllers\HR\PayrollController::class, 'payslip'])
                ->middleware('permission:payroll.view')->name('payslip');
        });

        // Pusat laporan biaya pegawai; middleware permission dan controller
        // keduanya menjaga agar data selalu terbatas pada company aktif.
        Route::get('/employee-costs', [\App\Http\Controllers\HR\EmployeeCostCenterController::class, 'index'])
            ->middleware('permission:employee-cost.view')->name('employee-costs.index');

        Route::prefix('operations')->name('operations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\HR\OperationsController::class,'index'])->middleware('permission:business-trip.view')->name('index');
            Route::get('/trips', [\App\Http\Controllers\HR\OperationsController::class,'index'])->middleware('permission:business-trip.view')->name('trips.index');
            Route::get('/vehicles', [\App\Http\Controllers\HR\OperationsController::class,'index'])->middleware('permission:vehicle-cost.view')->name('vehicles.index');
            Route::put('/policy/{policy}', [\App\Http\Controllers\HR\OperationsController::class,'updatePolicy'])->middleware('permission:business-trip.manage')->name('policy.update');
            Route::post('/trips', [\App\Http\Controllers\HR\OperationsController::class,'storeTrip'])->middleware('permission:business-trip.manage')->name('trips.store');
            Route::post('/trips/{trip}/approve-hr', [\App\Http\Controllers\HR\OperationsController::class,'approveHr'])->middleware('permission:business-trip.approve')->name('trips.approve-hr');
            Route::post('/trips/{trip}/approve-owner', [\App\Http\Controllers\HR\OperationsController::class,'approveOwner'])->middleware('permission:business-trip.approve')->name('trips.approve-owner');
            Route::post('/vehicles', [\App\Http\Controllers\HR\OperationsController::class,'storeVehicle'])->middleware('permission:vehicle-cost.manage')->name('vehicles.store');
            Route::post('/vehicles/plan-month', [\App\Http\Controllers\HR\OperationsController::class,'planVehicleMonth'])->middleware('permission:vehicle-cost.manage')->name('vehicles.plan-month');
        });

        /*
        |--------------------------------------------------------------------------
        | Employee Contract
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts', [EmployeeContractController::class, 'index'])
            ->middleware('permission:employees.view')
            ->name('contracts.index');

        Route::get('/contracts/create', [EmployeeContractController::class, 'create'])
            ->middleware('permission:employees.create')
            ->name('contracts.create');

        Route::post('/contracts', [EmployeeContractController::class, 'store'])
            ->middleware('permission:employees.create')
            ->name('contracts.store');

        Route::get('/contracts/{contract}', [EmployeeContractController::class, 'show'])
            ->middleware('permission:employees.view')
            ->name('contracts.show');

        /*
        |--------------------------------------------------------------------------
        | Extend Contract
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts/{contract}/extend', [EmployeeContractController::class, 'extend'])
            ->middleware('permission:employees.update')
            ->name('contracts.extend');

        Route::post('/contracts/{contract}/extend', [EmployeeContractController::class, 'storeExtension'])
            ->middleware('permission:employees.update')
            ->name('contracts.store-extension');

        /*
        |--------------------------------------------------------------------------
        | Edit
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts/{contract}/edit', [EmployeeContractController::class, 'edit'])
            ->middleware('permission:employees.update')
            ->name('contracts.edit');

        Route::put('/contracts/{contract}', [EmployeeContractController::class, 'update'])
            ->middleware('permission:employees.update')
            ->name('contracts.update');

        Route::delete('/contracts/{contract}', [EmployeeContractController::class, 'destroy'])
            ->middleware('permission:employees.delete')
            ->name('contracts.destroy');

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        Route::get('/contracts/{contract}/print',
    [EmployeeContractController::class,'print'])
    ->middleware('permission:employees.view')
    ->name('contracts.print');

        Route::get('/contracts/{contract}/pdf',
            [EmployeeContractController::class, 'pdf'])
            ->middleware('permission:employees.view')
            ->name('contracts.pdf');

        Route::post('/contracts/{contract}/approve',
            [EmployeeContractController::class, 'approve'])
            ->middleware('permission:employees.update')
            ->name('contracts.approve');

    });
});
