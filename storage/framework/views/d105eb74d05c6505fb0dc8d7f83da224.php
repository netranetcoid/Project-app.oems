<?php echo csrf_field(); ?>

<div class="row">

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Kode
        </label>

        <input
            type="text"
            name="code"
            class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('code', $contractType->code ?? '')); ?>"
            required>

        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback">
                <?php echo e($message); ?>

            </div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

    </div>

    <div class="col-md-8 mb-3">

        <label class="form-label">
            Nama Jenis Kontrak
        </label>

        <input
            type="text"
            name="name"
            class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('name', $contractType->name ?? '')); ?>"
            required>

        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback">
                <?php echo e($message); ?>

            </div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

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
            value="<?php echo e(old('default_duration_month', $contractType->default_duration_month ?? '')); ?>">

    </div>

    <div class="col-md-4 mb-3">

        <label class="form-label">
            Warna Badge
        </label>

        <select
            name="color"
            class="form-select">

            <?php
                $colors=[
                    'primary',
                    'success',
                    'warning',
                    'danger',
                    'info',
                    'secondary'
                ];
            ?>

            <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <option
                    value="<?php echo e($color); ?>"
                    <?php if(old('color',$contractType->color ?? 'primary')==$color): echo 'selected'; endif; ?>>

                    <?php echo e(ucfirst($color)); ?>


                </option>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                <?php if(old('is_active',$contractType->is_active ?? 1)==1): echo 'selected'; endif; ?>>

                Aktif

            </option>

            <option value="0"
                <?php if(old('is_active',$contractType->is_active ?? 1)==0): echo 'selected'; endif; ?>>

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
                <?php if(old('is_probation',$contractType->is_probation ?? false)): echo 'checked'; endif; ?>>

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
                <?php if(old('is_permanent',$contractType->is_permanent ?? false)): echo 'checked'; endif; ?>>

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
        class="form-control"><?php echo e(old('description',$contractType->description ?? '')); ?></textarea>

</div>

<div class="mt-4">

    <button
        class="btn btn-primary">

        <i class="ti ti-device-floppy"></i>

        Simpan

    </button>

    <a href="<?php echo e(route('master.contract-types.index')); ?>"
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
            class="form-control <?php $__errorArgs = ['template_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            value="<?php echo e(old('template_key', $contractType->template_key ?? '')); ?>"
            placeholder="probation / pkwt_1 / pkwt_2 / internship">
        <div class="form-text">Hanya identitas template, bukan kode program.</div>
        <?php $__errorArgs = ['template_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Isi Template / Addendum</label>
        <textarea
            name="template_body"
            rows="5"
            class="form-control <?php $__errorArgs = ['template_body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
            placeholder="Gunakan token aman seperti [[employee_name]], [[start_date]], [[end_date]], [[duration_month]]."><?php echo e(old('template_body', $contractType->template_body ?? '')); ?></textarea>
        <div class="form-text">Teks akan dicetak sebagai lampiran ketentuan khusus. Tidak dieksekusi sebagai PHP/Blade.</div>
        <?php $__errorArgs = ['template_body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>

<div class="mt-3">
    <label class="form-label">Dasar / Catatan Regulasi</label>
    <textarea
        name="legal_basis"
        rows="3"
        class="form-control <?php $__errorArgs = ['legal_basis'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
        placeholder="Contoh: PP 35/2021; review HR/legal sebelum diterbitkan."><?php echo e(old('legal_basis', $contractType->legal_basis ?? '')); ?></textarea>
    <div class="form-text">Catatan ini dapat diubah saat regulasi atau kebijakan perusahaan berubah.</div>
    <?php $__errorArgs = ['legal_basis'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\laragon\www\appoems\resources\views/master/contract-types/_form.blade.php ENDPATH**/ ?>