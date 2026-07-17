<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\KpiAspect;
use App\Models\KpiStandard;
use App\Models\Position;
use Illuminate\Database\Seeder;

class KpiFrameworkSeeder extends Seeder
{
    public function run(): void
    {
        $framework = [
            ['code'=>'DISCIPLINE','name'=>'Disiplin','category'=>'Kedisiplinan','weight'=>20,'description'=>'Kehadiran, ketepatan waktu, dan kepatuhan jadwal.'],
            ['code'=>'PRODUCTIVITY','name'=>'Produktivitas','category'=>'Kinerja','weight'=>30,'description'=>'Penyelesaian tiket, task, target, dan output kerja.'],
            ['code'=>'QUALITY','name'=>'Kualitas Kerja','category'=>'Kinerja','weight'=>30,'description'=>'Mutu hasil, reopen, komplain, akurasi, dan perbaikan.'],
            ['code'=>'SOP_ADMIN','name'=>'SOP dan Administrasi','category'=>'Kepatuhan','weight'=>10,'description'=>'Dokumentasi, laporan, eviden, dan kepatuhan SOP.'],
            ['code'=>'ATTITUDE','name'=>'Attitude dan Teamwork','category'=>'Perilaku','weight'=>10,'description'=>'Komunikasi, kolaborasi, tanggung jawab, dan sikap kerja.'],
        ];

        foreach (Company::query()->get() as $company) {
            $aspects = collect($framework)->mapWithKeys(function ($item) use ($company) {
                $aspect = KpiAspect::updateOrCreate(
                    ['company_id'=>$company->id,'code'=>$item['code']],
                    ['name'=>$item['name'],'category'=>$item['category'],'description'=>$item['description'],'is_active'=>true]
                );
                return [$item['code'] => ['model'=>$aspect,'weight'=>$item['weight']]];
            });

            foreach (Position::forCompany((int)$company->id)->active()->get() as $position) {
                $standard = KpiStandard::firstOrCreate(
                    ['company_id'=>$company->id,'position_id'=>$position->id,'name'=>'Framework KPI Standar'],
                    ['bonus_maximum'=>$position->default_kpi_incentive_max ?? 0,'is_active'=>true,'notes'=>'Framework default owner; HR dapat mengedit bobot dan batas bonus per jabatan.']
                );
                foreach ($aspects->values() as $order => $entry) {
                    $standard->items()->updateOrCreate(
                        ['kpi_aspect_id'=>$entry['model']->id],
                        ['aspect_name'=>$entry['model']->name,'guideline'=>$entry['model']->description,'weight'=>$entry['weight'],'sort_order'=>$order+1]
                    );
                }
            }
        }
    }
}
