@csrf

<div class="row">

    {{-- FOTO --}}
    <div class="col-lg-3">

        <div class="card">

            <div class="card-body text-center">

                @if(isset($employee) && $employee->photo)

                    <img
                        src="{{ $employee->photo_url }}"
                        class="img-fluid rounded mb-3"
                        style="max-height:220px">

                @else

                    <img
                        src="{{ asset('assets/img/avatars/1.png') }}"
                        class="img-fluid rounded mb-3"
                        style="max-height:220px">

                @endif

                <input
                    type="file"
                    name="photo"
                    class="form-control @error('photo') is-invalid @enderror">

                @error('photo')

                    <div class="invalid-feedback">

                        {{ $message }}

                    </div>

                @enderror

            </div>

        </div>

    </div>

    <div class="col-lg-9">

        <div class="card">

            <div class="card-header">

                <h5 class="mb-0">

                    Informasi Pegawai

                </h5>

            </div>

            <div class="card-body">

                <div class="row">
                                        {{-- Nomor Pegawai --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Nomor Pegawai

                        </label>

                        <input
                            type="text"
                            name="employee_no"
                            class="form-control"
                            value="{{ old('employee_no',$employee->employee_no ?? '') }}"
                            placeholder="Otomatis jika kosong">

                    </div>

                    {{-- Nama --}}

                    <div class="col-md-8 mb-3">

                        <label class="form-label">

                            Nama Lengkap

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name',$employee->name ?? '') }}"
                            required>

                        @error('name')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Alias --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Nama Panggilan

                        </label>

                        <input
                            type="text"
                            name="nickname"
                            class="form-control"
                            value="{{ old('nickname',$employee->nickname ?? '') }}">

                    </div>

                    {{-- Email --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Email

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email',$employee->email ?? '') }}">

                    </div>

                    {{-- HP --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Nomor HP

                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="{{ old('phone',$employee->phone ?? '') }}">

                    </div>

                    {{-- WhatsApp --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            WhatsApp

                        </label>

                        <input
                            type="text"
                            name="whatsapp"
                            class="form-control"
                            value="{{ old('whatsapp',$employee->whatsapp ?? '') }}">

                    </div>
                                        {{-- Jenis Kelamin --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Jenis Kelamin
                        </label>

                        <select
                            name="gender"
                            class="form-select">

                            <option value="">- Pilih -</option>

                            <option
                                value="male"
                                @selected(old('gender',$employee->gender ?? '')=='male')>

                                Laki-laki

                            </option>

                            <option
                                value="female"
                                @selected(old('gender',$employee->gender ?? '')=='female')>

                                Perempuan

                            </option>

                        </select>

                    </div>

                    {{-- Tempat Lahir --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tempat Lahir

                        </label>

                        <input
                            type="text"
                            name="birth_place"
                            class="form-control"
                            value="{{ old('birth_place',$employee->birth_place ?? '') }}">

                    </div>

                    {{-- Tanggal Lahir --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Lahir

                        </label>

                        <input
                            type="date"
                            name="birth_date"
                            class="form-control"
                            value="{{ old('birth_date',isset($employee)?optional($employee->birth_date)->format('Y-m-d'):'') }}">

                    </div>

                    {{-- Tanggal Masuk --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Tanggal Masuk

                        </label>

                        <input
                            type="date"
                            name="join_date"
                            class="form-control @error('join_date') is-invalid @enderror"
                            value="{{ old('join_date',isset($employee)?optional($employee->join_date)->format('Y-m-d'):'') }}"
                            required>

                        @error('join_date')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                    {{-- Status Pegawai --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Status Pegawai

                        </label>

                        <select
                            name="employment_status"
                            class="form-select">

                            <option
                                value="probation"
                                @selected(old('employment_status',$employee->employment_status ?? '')=='probation')>

                                Probation

                            </option>

                            <option
                                value="contract"
                                @selected(old('employment_status',$employee->employment_status ?? '')=='contract')>

                                Kontrak

                            </option>

                            <option
                                value="permanent"
                                @selected(old('employment_status',$employee->employment_status ?? '')=='permanent')>

                                Tetap

                            </option>

                            <option
                                value="resign"
                                @selected(old('employment_status',$employee->employment_status ?? '')=='resign')>

                                Resign

                            </option>

                        </select>

                    </div>

                    {{-- Status Kerja --}}
                    <div class="col-md-4 mb-3">

                        <label class="form-label">

                            Status Kerja

                        </label>

                        <select
                            name="work_status"
                            class="form-select">

                            <option
                                value="active"
                                @selected(old('work_status',$employee->work_status ?? 'active')=='active')>

                                Aktif

                            </option>

                            <option
                                value="inactive"
                                @selected(old('work_status',$employee->work_status ?? '')=='inactive')>

                                Non Aktif

                            </option>

                        </select>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Alamat & Kontak Darurat</h5>
        <small class="text-muted">Data ini dibatasi company aktif dan digunakan untuk profil, kontrak, serta kesiapan BPJS.</small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Alamat KTP</label><textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address',$employee->address ?? '') }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6 mb-3"><label class="form-label">Alamat Domisili</label><textarea name="domicile_address" rows="3" class="form-control @error('domicile_address') is-invalid @enderror">{{ old('domicile_address',$employee->domicile_address ?? '') }}</textarea>@error('domicile_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-3 mb-3"><label class="form-label">Provinsi</label><input type="text" name="province" class="form-control" value="{{ old('province',$employee->province ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Kota / Kabupaten</label><input type="text" name="city" class="form-control" value="{{ old('city',$employee->city ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Kecamatan</label><input type="text" name="district" class="form-control" value="{{ old('district',$employee->district ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Kelurahan / Desa</label><input type="text" name="village" class="form-control" value="{{ old('village',$employee->village ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Kode Pos</label><input type="text" name="postal_code" class="form-control" inputmode="numeric" value="{{ old('postal_code',$employee->postal_code ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Kontak Darurat</label><input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name',$employee->emergency_contact_name ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">Hubungan</label><input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation',$employee->emergency_contact_relation ?? '') }}"></div>
            <div class="col-md-3 mb-3"><label class="form-label">No. Kontak Darurat</label><input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone',$employee->emergency_contact_phone ?? '') }}"></div>
            <div class="col-12 mb-0"><label class="form-label">Alamat Kontak Darurat</label><textarea name="emergency_contact_address" rows="2" class="form-control">{{ old('emergency_contact_address',$employee->emergency_contact_address ?? '') }}</textarea></div>
        </div>
    </div>
</div>
<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">

            Struktur Organisasi

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            {{-- Site --}}
            <div class="col-md-4 mb-3">

                <label class="form-label">

                    Site

                </label>

                <select
                    name="branch_id"
                    class="form-select">

                    <option value="">

                        -- Pilih Site --

                    </option>

                    @foreach($branches as $branch)

                        <option
                            value="{{ $branch->id }}"
                            @selected(old('branch_id',$employee->branch_id ?? '')==$branch->id)>

                            {{ $branch->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Divisi --}}
            <div class="col-md-4 mb-3">

                <label class="form-label">

                    Divisi

                </label>

                <select
                    name="division_id"
                    class="form-select">

                    <option value="">

                        -- Pilih Divisi --

                    </option>

                    @foreach($divisions as $division)

                        <option
                            value="{{ $division->id }}"
                            @selected(old('division_id',$employee->division_id ?? '')==$division->id)>

                            {{ $division->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Jabatan --}}
            <div class="col-md-4 mb-3">

                <label class="form-label">

                    Jabatan

                </label>

                <select
                    name="position_id"
                    class="form-select">

                    <option value="">

                        -- Pilih Jabatan --

                    </option>

                    @foreach($positions as $position)

                        <option
                            value="{{ $position->id }}"
                            @selected(old('position_id',$employee->position_id ?? '')==$position->id)>

                            {{ $position->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Supervisor --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Supervisor

                </label>

                <select
                    name="supervisor_employee_id"
                    class="form-select">

                    <option value="">

                        -- Tidak Ada --

                    </option>

                    @foreach($supervisors as $supervisor)

                        <option
                            value="{{ $supervisor->id }}"
                            @selected(old('supervisor_employee_id',$employee->supervisor_employee_id ?? '')==$supervisor->id)>

                            {{ $supervisor->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Manager --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Manager

                </label>

                <select
                    name="manager_employee_id"
                    class="form-select">

                    <option value="">

                        -- Tidak Ada --

                    </option>

                    @foreach($managers as $manager)

                        <option
                            value="{{ $manager->id }}"
                            @selected(old('manager_employee_id',$employee->manager_employee_id ?? '')==$manager->id)>

                            {{ $manager->name }}

                        </option>

                    @endforeach

                </select>

            </div>

            {{-- Login OEMS --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Akun OvallHR

                </label>

                <div class="form-check form-switch mt-2">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        value="1"
                        name="create_login"
                        id="create_login"
                        @checked(old('create_login'))

                    >

                    <label
                        class="form-check-label"
                        for="create_login">

                        Akun dibuat otomatis dari email pegawai saat data disimpan

                    </label>

                </div>

            </div>

            {{-- Role --}}
            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Role OEMS

                </label>

                <select
                    name="role"
                    class="form-select">

                    <option value="">

                        -- Pilih Role --

                    </option>

                    @foreach($roles as $role)

                        <option
                            value="{{ $role->name }}"
                            @selected(old('role')==$role->name)>

                            {{ $role->name }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

    </div>

</div>
<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">

            Payroll & Benefit

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            {{-- Gaji Pokok --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Gaji Pokok

                </label>

                <input
                    type="text"
                    name="basic_salary"
                    class="form-control"
                    inputmode="numeric"
                    data-rupiah="true"
                    value="{{ old('basic_salary',$employee->basic_salary ?? 0) }}">

            </div>


            {{-- Tunjangan Tetap untuk dasar upah BPJS --}}
            <div class="col-md-3 mb-3">
                <label class="form-label">Tunjangan Tetap <small class="text-muted">(Dasar BPJS)</small></label>
                <input type="text" name="fixed_allowance" class="form-control" inputmode="numeric" data-rupiah="true" value="{{ old('fixed_allowance',$employee->fixed_allowance ?? 0) }}">
                <small class="text-muted">Hanya komponen tetap yang masuk dasar perhitungan BPJS.</small>
            </div>            {{-- Uang Makan --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Uang Makan

                </label>

                <input
                    type="text"
                    name="meal_allowance"
                    class="form-control"
                    inputmode="numeric"
                    data-rupiah="true"
                    value="{{ old('meal_allowance',$employee->meal_allowance ?? 0) }}">

            </div>

            {{-- Uang Transport --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Uang Transport

                </label>

                <input
                    type="text"
                    name="transport_allowance"
                    class="form-control"
                    inputmode="numeric"
                    data-rupiah="true"
                    value="{{ old('transport_allowance',$employee->transport_allowance ?? 0) }}">

            </div>

            {{-- Tunjangan Jabatan --}}
            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Tunjangan Jabatan

                </label>

                <input
                    type="text"
                    name="position_allowance"
                    class="form-control"
                    inputmode="numeric"
                    data-rupiah="true"
                    value="{{ old('position_allowance',$employee->position_allowance ?? 0) }}">

            </div>

        </div>

    </div>

</div>

<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">

            Catatan

        </h5>

    </div>

    <div class="card-body">

        <textarea
            name="notes"
            rows="5"
            class="form-control"
            placeholder="Catatan tambahan...">{{ old('notes',$employee->notes ?? '') }}</textarea>

    </div>

</div>
<div class="card mt-4">

    <div class="card-header">

        <h5 class="mb-0">

            Aksi

        </h5>

    </div>

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-6">

                <small class="text-muted">

                    Pastikan seluruh data pegawai sudah benar sebelum disimpan.

                </small>

            </div>

            <div class="col-md-6 text-end">

                <a
                    href="{{ route('employees.index') }}"
                    class="btn btn-outline-secondary">

                    <i class="ti ti-arrow-left"></i>

                    Kembali

                </a>

                <button
                    type="reset"
                    class="btn btn-warning">

                    <i class="ti ti-refresh"></i>

                    Reset

                </button>

                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="ti ti-device-floppy"></i>

                    @isset($employee)

                        Update Pegawai

                    @else

                        Simpan Pegawai

                    @endisset

                </button>

            </div>

        </div>

    </div>

</div>
@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Create Login
    |--------------------------------------------------------------------------
    */

    const createLogin = document.getElementById('create_login');

    const roleSelect = document.querySelector('select[name="role"]');

    function toggleRole()
    {
        if (!createLogin || !roleSelect) {
            return;
        }

        roleSelect.disabled = !createLogin.checked;

        if (!createLogin.checked) {

            roleSelect.value = '';

        }
    }

    if (createLogin) {

        toggleRole();

        createLogin.addEventListener('change', toggleRole);

    }

    /*
    |--------------------------------------------------------------------------
    | Preview Photo
    |--------------------------------------------------------------------------
    */

    const photoInput = document.querySelector('input[name="photo"]');

    const previewImage = document.querySelector('img');

    if (photoInput && previewImage) {

        photoInput.addEventListener('change', function(e){

            const file = e.target.files[0];

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(ev){

                previewImage.src = ev.target.result;

            };

            reader.readAsDataURL(file);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Format Nominal
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        'input[name="basic_salary"],\
         input[name="meal_allowance"],\
         input[name="transport_allowance"],\
         input[name="position_allowance"],\
         input[name="fixed_allowance"]'
    ).forEach(function(el){

        el.addEventListener('focus',function(){

            if(this.value==0){

                this.value='';

            }

        });

        el.addEventListener('blur',function(){

            if(this.value==''){

                this.value=0;

            }

        });

    });

});

</script>

@endpush
