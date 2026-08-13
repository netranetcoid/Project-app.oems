<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\CleaningDutyItem;
use App\Models\CleaningDutyLog;
use App\Models\CleaningDutySchedule;
use App\Models\Division;
use App\Models\Employee;
use App\Services\Employee\CleaningDutyPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CleaningDutyController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = (int) session('company_id');
        $divisionId = $request->integer('division_id');
        return view('hr.cleaning-duty.index', [
            'divisions' => Division::query()->forCompany($companyId)->active()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()->forCompany($companyId)->active()->with('division:id,name')->orderBy('name')->get(['id', 'name', 'employee_no', 'division_id']),
            'schedules' => CleaningDutySchedule::query()->forCompany($companyId)->with(['division:id,name', 'employee:id,name,employee_no', 'items'])->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))->orderBy('division_id')->get(),
            'logs' => CleaningDutyLog::query()->forCompany($companyId)->with(['schedule', 'division:id,name', 'employee:id,name,employee_no', 'reviewer:id,name', 'items.item'])->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))->latest('duty_date')->limit(100)->get(),
            'divisionId' => $divisionId,
        ]);
    }

    public function store(Request $request, CleaningDutyPublisher $publisher): RedirectResponse
    {
        $companyId = (int) session('company_id');
        $data = $request->validate([
            'division_id' => ['required', Rule::exists('divisions', 'id')->where('company_id', $companyId)],
            'employee_ids' => ['required', 'array', 'min:1'], 'employee_ids.*' => ['integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'duty_type' => ['required', Rule::in(['daily_area', 'server_room'])], 'recurrence' => ['required', Rule::in(['weekly', 'monthly'])],
            'weekday' => ['nullable', 'integer', 'between:1,7'], 'day_of_month' => ['nullable', 'integer', 'between:1,28'],
            'title' => ['required', 'string', 'max:160'], 'instructions' => ['nullable', 'string', 'max:2000'],
            'starts_on' => ['required', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'item_names' => ['nullable', 'array'], 'item_names.*' => ['nullable', 'string', 'max:180'],
            'item_weights' => ['nullable', 'array'], 'item_weights.*' => ['nullable', 'integer', 'between:0,100'],
        ]);
        if (($data['recurrence'] === 'weekly' && empty($data['weekday'])) || ($data['recurrence'] === 'monthly' && empty($data['day_of_month']))) {
            return back()->withInput()->withErrors(['recurrence' => 'Pilih hari untuk mingguan atau tanggal 1-28 untuk bulanan.']);
        }
        $employees = Employee::query()->forCompany($companyId)->where('division_id', $data['division_id'])->whereIn('id', $data['employee_ids'])->pluck('id');
        if ($employees->count() !== count(array_unique($data['employee_ids']))) return back()->withInput()->withErrors(['employee_ids' => 'Pegawai harus berasal dari divisi yang dipilih.']);
        $items = collect($data['item_names'] ?? [])->map(fn ($name, $i) => ['name' => trim((string) $name), 'weight' => (int) ($data['item_weights'][$i] ?? 0)])->filter(fn ($item) => $item['name'] !== '')->values();
        if ($data['duty_type'] === 'server_room' && $items->isEmpty()) return back()->withInput()->withErrors(['item_names' => 'Piket server wajib memiliki minimal satu item pemeriksaan.']);
        if ($items->sum('weight') > 100) return back()->withInput()->withErrors(['item_weights' => 'Jumlah bobot checklist tidak boleh melebihi 100%.']);
        foreach ($employees as $employeeId) {
            $schedule = CleaningDutySchedule::query()->create([
                'company_id' => $companyId, 'division_id' => $data['division_id'], 'employee_id' => $employeeId,
                'duty_type' => $data['duty_type'], 'recurrence' => $data['recurrence'],
                'weekday' => $data['recurrence'] === 'weekly' ? $data['weekday'] : null,
                'day_of_month' => $data['recurrence'] === 'monthly' ? $data['day_of_month'] : null,
                'title' => $data['title'], 'instructions' => $data['instructions'] ?? null,
                'starts_on' => $data['starts_on'], 'ends_on' => $data['ends_on'] ?? null, 'is_active' => true,
            ]);
            foreach ($items as $index => $item) $schedule->items()->create(['item_name' => $item['name'], 'weight' => $item['weight'], 'sort_order' => $index + 1]);
        }
        $publisher->publishForToday();
        return back()->with('success', $employees->count() . ' jadwal dan tugas piket berhasil dibuat.');
    }

    public function addItem(Request $request, CleaningDutySchedule $schedule): RedirectResponse
    {
        $this->guardCompany($schedule->company_id);
        $data = $request->validate(['item_name' => ['required', 'string', 'max:180'], 'weight' => ['required', 'integer', 'between:0,100']]);
        if ($schedule->items()->sum('weight') + $data['weight'] > 100) return back()->withErrors(['weight' => 'Total bobot checklist tidak boleh melebihi 100%.']);
        $schedule->items()->create($data + ['sort_order' => $schedule->items()->max('sort_order') + 1]);
        return back()->with('success', 'Item checklist ditambahkan.');
    }

    public function deleteItem(CleaningDutyItem $item): RedirectResponse
    {
        $this->guardCompany((int) $item->schedule()->value('company_id')); $item->delete();
        return back()->with('success', 'Item checklist dihapus.');
    }

    public function updateItem(Request $request, CleaningDutyItem $item): RedirectResponse
    {
        $this->guardCompany((int) $item->schedule()->value('company_id'));
        $data = $request->validate(['item_name' => ['required', 'string', 'max:180'], 'weight' => ['required', 'integer', 'between:0,100']]);
        $otherWeight = (int) $item->schedule->items()->whereKeyNot($item->id)->sum('weight');
        if ($otherWeight + $data['weight'] > 100) return back()->withErrors(['weight' => 'Total bobot checklist tidak boleh melebihi 100%.']);
        $item->update($data);
        return back()->with('success', 'Item dan bobot diperbarui.');
    }

    public function toggle(CleaningDutySchedule $schedule): RedirectResponse { $this->guardCompany($schedule->company_id); $schedule->update(['is_active' => ! $schedule->is_active]); return back()->with('success', 'Status jadwal diperbarui.'); }
    public function destroy(CleaningDutySchedule $schedule): RedirectResponse { $this->guardCompany($schedule->company_id); $schedule->delete(); return back()->with('success', 'Jadwal dihapus.'); }

    public function complete(Request $request, CleaningDutyLog $log, CleaningDutyPublisher $publisher): RedirectResponse
    {
        $this->guardCompany($log->company_id);
        $data = $request->validate(['completion_note' => ['required', 'string', 'max:1000'], 'completed_items' => ['nullable', 'array'], 'completed_items.*' => ['integer']]);
        $selected = collect($data['completed_items'] ?? [])->map(fn ($id) => (int) $id);
        $log->items()->each(fn ($row) => $row->update(['is_completed' => $selected->contains($row->cleaning_duty_item_id)]));
        $score = $log->items()->with('item')->get()->filter->is_completed->sum(fn ($row) => (int) $row->item?->weight);
        $log->update(['status' => 'completed', 'score' => $score, 'completion_note' => $data['completion_note'], 'completed_at' => now(), 'reviewed_by' => $request->user()->id]);
        $log->task?->update(['status' => 'completed', 'completed_at' => now()]);
        $publisher->syncAnnouncements($log->fresh(['schedule', 'division', 'employee']));
        return back()->with('success', 'Piket diverifikasi selesai. Nilai checklist: ' . $score . '.');
    }

    private function guardCompany(int $companyId): void { abort_unless($companyId === (int) session('company_id'), 404); }
}
