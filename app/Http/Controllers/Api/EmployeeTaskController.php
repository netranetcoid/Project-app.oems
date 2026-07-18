<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeTaskController extends Controller
{
    /** Karyawan hanya bisa membaca tugasnya sendiri. */
    public function index(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $items = EmployeeTask::query()->where('company_id', $employee->company_id)
            ->where('employee_id', $employee->id)->whereNotIn('status', ['cancelled'])
            ->orderByRaw("CASE WHEN status IN ('assigned', 'in_progress') THEN 0 ELSE 1 END")
            ->orderBy('due_at')->limit(100)->get()
            ->map(fn (EmployeeTask $task) => $this->payload($task));
        return response()->json(['data' => ['items' => $items]]);
    }

    /** Status pekerjaan dicatat dengan timestamp untuk bahan KPI/audit. */
    public function updateStatus(Request $request, EmployeeTask $task): JsonResponse
    {
        $employee = $this->employee($request);
        abort_unless((int) $task->company_id === (int) $employee->company_id && (int) $task->employee_id === (int) $employee->id, 403);
        $data = $request->validate(['status' => ['required', Rule::in(['in_progress', 'completed'])]]);
        if ($task->status === 'completed') abort(422, 'Tugas sudah selesai dan tidak dapat diubah dari aplikasi.');
        $task->update([
            'status' => $data['status'],
            'started_at' => $data['status'] === 'in_progress' ? ($task->started_at ?: now()) : $task->started_at,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);
        return response()->json(['data' => $this->payload($task->fresh())]);
    }

    private function employee(Request $request)
    {
        $employee = $request->user()?->employee;
        abort_unless($employee && (int) $employee->company_id === (int) $request->user()->company_id, 403, 'Akun tidak terhubung ke data karyawan.');
        return $employee;
    }

    private function payload(EmployeeTask $task): array
    {
        return ['id' => $task->id, 'title' => $task->title, 'description' => $task->description,
            'priority' => $task->priority, 'status' => $task->status,
            'due_at' => $task->due_at?->toIso8601String(), 'started_at' => $task->started_at?->toIso8601String(),
            'completed_at' => $task->completed_at?->toIso8601String()];
    }
}
