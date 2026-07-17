@csrf

<div class="row">

    <div class="col-md-6 mb-3">

        <label class="form-label">

            Pegawai

        </label>

        <select
            name="employee_id"
            class="form-select"
            required>

            <option value="">

                -- Pilih Pegawai --

            </option>

            @foreach($employees as $employee)

                <option
                    value="{{ $employee->id }}"
                    @selected(old('employee_id',$contract->employee_id ?? '')==$employee->id)>

                    {{ $employee->employee_no }}

                    -

                    {{ $employee->name }}

                </option>

            @endforeach

        </select>

    </div>

    <div class="col-md-6 mb-3">

        <label class="form-label">

            Jenis Kontrak

        </label>

        <select
            name="contract_type_id"
            class="form-select"
            required>

            <option value="">

                -- Pilih Jenis --

            </option>

            @foreach($contractTypes as $type)

                <option
                    value="{{ $type->id }}"
                    @selected(old('contract_type_id',$contract->contract_type_id ?? '')==$type->id)>

                    {{ $type->name }}

                </option>

            @endforeach

        </select>

    </div>

</div>

<div class="row">

    <div class="col-md-4">

        <label class="form-label">

            Tanggal Mulai

        </label>

        <input
            type="date"
            name="start_date"
            class="form-control"
            value="{{ old('start_date',$contract->start_date ?? '') }}"
            required>

    </div>

    <div class="col-md-4">

        <label class="form-label">

            Tanggal Selesai

        </label>

        <input
            type="date"
            name="end_date"
            class="form-control"
            value="{{ old('end_date',$contract->end_date ?? '') }}">

    </div>

    <div class="col-md-4">

        <label class="form-label">

            Lama (Bulan)

        </label>

        <input
            type="number"
            name="duration_month"
            class="form-control"
            value="{{ old('duration_month',$contract->duration_month ?? '') }}">

    </div>

</div>

<div class="row mt-3">

    <div class="col-md-12">

        <label class="form-label">

            Catatan

        </label>

        <textarea
            name="notes"
            rows="4"
            class="form-control">{{ old('notes',$contract->notes ?? '') }}</textarea>

    </div>

</div>

<div class="mt-4">

    <button
        class="btn btn-primary">

        <i class="ti ti-device-floppy"></i>

        Simpan Kontrak

    </button>

    <a
        href="{{ route('hr.contracts.index') }}"
        class="btn btn-label-secondary">

        Kembali

    </a>

</div>