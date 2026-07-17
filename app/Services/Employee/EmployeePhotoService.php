<?php

namespace App\Services\Employee;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeePhotoService
{
    /**
     * Upload Photo Employee
     */
    public function upload(
        UploadedFile $file,
        int $companyId
    ): string {

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $filename =
            now()->format('YmdHis') .
            '_' .
            Str::uuid() .
            '.' .
            $extension;

        return $file->storeAs(
            "employees/{$companyId}",
            $filename,
            'public'
        );
    }

    /**
     * Replace Photo Employee
     */
    public function replace(
        ?string $oldPhoto,
        UploadedFile $file,
        int $companyId
    ): string {

        if (
            !empty($oldPhoto) &&
            Storage::disk('public')->exists($oldPhoto)
        ) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return $this->upload(
            $file,
            $companyId
        );
    }

    /**
     * Delete Photo Employee
     */
    public function delete(
        ?string $photo
    ): void {

        if (
            !empty($photo) &&
            Storage::disk('public')->exists($photo)
        ) {
            Storage::disk('public')->delete($photo);
        }
    }
}