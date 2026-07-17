<?php

namespace App\Services\Contract;

use App\Models\ContractType;
use Illuminate\Support\Facades\DB;

class ContractTypeService
{
    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(array $data): ContractType
    {
        return DB::transaction(function () use ($data) {

            $companyId = (int) session('company_id');
            if (!$companyId) {
                throw new \RuntimeException('Company aktif belum dipilih.');
            }
            $data['company_id'] = $companyId;

            return ContractType::create($data);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        ContractType $contractType,
        array $data
    ): ContractType {

        return DB::transaction(function () use (
            $contractType,
            $data
        ) {

            if ((int) $contractType->company_id !== (int) session('company_id')) {
                abort(403, 'Template kontrak bukan bagian dari company aktif.');
            }

            $contractType->update($data);

            return $contractType;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        ContractType $contractType
    ): void {

        DB::transaction(function () use ($contractType) {

            /*
            |--------------------------------------------------------------------------
            | Cek apakah sudah dipakai kontrak
            |--------------------------------------------------------------------------
            */

            if (
                $contractType->contracts()->exists()
            ) {

                abort(
                    422,
                    'Jenis kontrak sudah digunakan.'
                );

            }

            $contractType->delete();

        });

    }
}
