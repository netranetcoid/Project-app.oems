@csrf

<div class="row">

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Kode
        </label>

        <input
            type="text"
            name="code"
            class="form-control @error('code') is-invalid @enderror"
            value="{{ old('code', $contractType->code ?? '') }}"
            required>

        @error('code')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

    <div class="col-md-8 mb-3">

        <label class="form-label">
            Nama Jenis Kontrak
        </label>

        <input
            type="text"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $contractType->name ?? '') }}"
            required>

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Durasi (Bulan)
        </label>

        <input
            type="number"
            name="default_duration_month"
            class="form-control"
            value="{{ old('default_duration_month', $contractType->default_duration_month ?? '') }}">

    </div>

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Warna Badge
        </label>

        <select
            name="color"
            class="form-select">

            @php
                $colors=[
                    'primary',
                    'success',
                    'warning',
                    'danger',
                    'info',
                    'secondary'
                ];
            @endphp

            @foreach($colors as $color)

                <option
                    value="{{ $color }}"
                    @selected(old('color',$contractType->color ?? 'primary')==$color)>

                    {{ ucfirst($color) }}

                </option>

            @endforeach

        </select>

    </div>

    <div class="col-md-4">

        <label class="form-label">

            Status

        </label>

        <select
            name="is_active"
            class="form-select">

            <option value="1"
                @selected(old('is_active',$contractType->is_active ?? 1)==1)>

                Aktif

            </option>

            <option value="0"
                @selected(old('is_active',$contractType->is_active ?? 1)==0)>

                Non Aktif

            </option>

        </select>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <div class="form-check">

            <input
                class="form-check-input"
                type="checkbox"
                name="is_probation"
                value="1"
                @checked(old('is_probation',$contractType->is_probation ?? false))>

            <label class="form-check-label">

                Jenis Probation

            </label>

        </div>

    </div>

    <div class="col-md-6">

        <div class="form-check">

            <input
                class="form-check-input"
                type="checkbox"
                name="is_permanent"
                value="1"
                @checked(old('is_permanent',$contractType->is_permanent ?? false))>

            <label class="form-check-label">

                Pegawai Tetap

            </label>

        </div>

    </div>

</div>

<div class="mt-3">

    <label class="form-label">

        Deskripsi

    </label>

    <textarea
        name="description"
        rows="4"
        class="form-control">{{ old('description',$contractType->description ?? '') }}</textarea>

</div>

<div class="mt-4">

    <button
        class="btn btn-primary">

        <i class="ti ti-device-floppy"></i>

        Simpan

    </button>

    <a href="{{ route('master.contract-types.index') }}"
       class="btn btn-label-secondary">

        Kembali

    </a>

</div>

<div class="row mt-3">
    <div class="col-md-4 mb-3">
        <label class="form-label">Kunci Template</label>
        <input
            type="text"
            name="template_key"
            class="form-control @error('template_key') is-invalid @enderror"
            value="{{ old('template_key', $contractType->template_key ?? '') }}"
            placeholder="probation / pkwt_1 / pkwt_2 / internship">
        <div class="form-text">Hanya identitas template, bukan kode program.</div>
        @error('template_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Isi Template / Addendum</label>
        <textarea
            name="template_body"
            rows="5"
            class="form-control @error('template_body') is-invalid @enderror"
            placeholder="Gunakan token aman seperti [[employee_name]], [[start_date]], [[end_date]], [[duration_month]].">{{ old('template_body', $contractType->template_body ?? '') }}</textarea>
        <div class="form-text">Teks akan dicetak sebagai lampiran ketentuan khusus. Tidak dieksekusi sebagai PHP/Blade.</div>
        @error('template_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mt-3">
    <label class="form-label">Dasar / Catatan Regulasi</label>
    <textarea
        name="legal_basis"
        rows="3"
        class="form-control @error('legal_basis') is-invalid @enderror"
        placeholder="Contoh: PP 35/2021; review HR/legal sebelum diterbitkan.">{{ old('legal_basis', $contractType->legal_basis ?? '') }}</textarea>
    <div class="form-text">Catatan ini dapat diubah saat regulasi atau kebijakan perusahaan berubah.</div>
    @error('legal_basis')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
