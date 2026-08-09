<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\EbupotVendor;
use App\Models\EbupotVendorRecord;
use App\Services\Tax\EbupotVendorService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EbupotVendorController extends Controller
{
    public function __construct(private readonly EbupotVendorService $service) {}
    private function companyId(): int { return (int) session('company_id'); }
    private function ownVendor(EbupotVendor $vendor): void { abort_if($vendor->company_id !== $this->companyId(), 403); }
    private function ownRecord(EbupotVendorRecord $record): void { abort_if($record->company_id !== $this->companyId(), 403); }

    public function index(Request $request): View
    {
        $period = Carbon::createFromFormat('Y-m', (string) $request->string('period', now()->format('Y-m')))->startOfMonth();
        $this->service->ensurePeriod($this->companyId(), $period);
        $records = EbupotVendorRecord::forCompany($this->companyId())->with('vendor')->whereDate('period', $period)->orderBy('status')->get();
        return view('finance.ebupot-vendors.index', [
            'period'=>$period, 'records'=>$records, 'vendors'=>EbupotVendor::forCompany($this->companyId())->orderBy('name')->get(),
            'settings'=>$this->service->settings($this->companyId()), 'checks'=>EbupotVendorService::CHECKS,
            'summary'=>['total'=>$records->count(),'waiting'=>$records->whereIn('status',['waiting_data','draft'])->count(),'issued'=>$records->where('status','issued')->count(),'sent'=>$records->where('status','sent')->count(),'tax'=>$records->sum('tax_amount')],
        ]);
    }

    public function storeVendor(Request $request): RedirectResponse
    {
        $data=$request->validate(['name'=>'required|string|max:255','npwp'=>'nullable|string|max:32','nitku'=>'nullable|string|max:32','pic_name'=>'nullable|string|max:255','whatsapp'=>'nullable|string|max:32','email'=>'nullable|email|max:255','service_name'=>'nullable|string|max:255','tax_article'=>'required|string|max:40','tax_object_code'=>'nullable|string|max:40','default_tax_rate'=>'required|numeric|min:0|max:100','has_tax_facility'=>'nullable|boolean','tax_facility_notes'=>'nullable|string','notes'=>'nullable|string','initial_period'=>'required|date_format:Y-m','invoice_number'=>'nullable|string|max:255','invoice_date'=>'nullable|date','due_date'=>'nullable|date','tax_base'=>'nullable|numeric|min:0','vat_amount'=>'nullable|numeric|min:0','invoice_total'=>'nullable|numeric|min:0','has_stamp'=>'nullable|boolean','invoice_file'=>'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png']);
        DB::transaction(function() use ($request,$data): void {
            $next=(int)EbupotVendor::withTrashed()->max('id')+1;
            $vendorData=collect($data)->except(['initial_period','invoice_number','invoice_date','due_date','tax_base','vat_amount','invoice_total','has_stamp','invoice_file'])->all();
            $vendor=EbupotVendor::create($vendorData+['company_id'=>$this->companyId(),'code'=>'VND-'.str_pad((string)$next,4,'0',STR_PAD_LEFT),'has_tax_facility'=>$request->boolean('has_tax_facility'),'is_active'=>true]);
            $amounts=['tax_base'=>(float)($data['tax_base']??0),'tax_rate'=>(float)$vendor->default_tax_rate,'vat_amount'=>(float)($data['vat_amount']??0),'stamp_amount'=>$request->boolean('has_stamp')?10000:0,'invoice_total'=>(float)($data['invoice_total']??0)];
            $record=EbupotVendorRecord::create($amounts+$this->service->calculate($amounts)+['company_id'=>$this->companyId(),'ebupot_vendor_id'=>$vendor->id,'period'=>Carbon::createFromFormat('Y-m',$data['initial_period'])->startOfMonth(),'invoice_number'=>$data['invoice_number']??null,'invoice_date'=>$data['invoice_date']??null,'due_date'=>$data['due_date']??null,'status'=>'waiting_data','checklist'=>array_fill_keys(EbupotVendorService::CHECKS,false),'updated_by'=>$request->user()->id]);
            if($request->hasFile('invoice_file'))$record->update(['invoice_file'=>$request->file('invoice_file')->store('private/ebupot/'.$this->companyId().'/'.$record->id)]);
        });
        return back()->with('success','Vendor berhasil ditambahkan.');
    }

    public function updateVendor(Request $request, EbupotVendor $vendor): RedirectResponse
    {
        $this->ownVendor($vendor); $data=$request->validate(['name'=>'required|string|max:255','npwp'=>'nullable|string|max:32','nitku'=>'nullable|string|max:32','pic_name'=>'nullable|string|max:255','whatsapp'=>'nullable|string|max:32','email'=>'nullable|email|max:255','service_name'=>'nullable|string|max:255','tax_article'=>'required|string|max:40','tax_object_code'=>'nullable|string|max:40','default_tax_rate'=>'required|numeric|min:0|max:100','has_tax_facility'=>'nullable|boolean','tax_facility_notes'=>'nullable|string','notes'=>'nullable|string','is_active'=>'nullable|boolean']);
        $vendor->update($data+['has_tax_facility'=>$request->boolean('has_tax_facility'),'is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Data vendor diperbarui.');
    }

    public function updateRecord(Request $request, EbupotVendorRecord $record): RedirectResponse
    {
        $this->ownRecord($record); $data=$request->validate(['invoice_number'=>'nullable|string|max:255','invoice_date'=>'nullable|date','due_date'=>'nullable|date','tax_base'=>'required|numeric|min:0','tax_rate'=>'required|numeric|min:0|max:100','vat_amount'=>'nullable|numeric|min:0','stamp_amount'=>'nullable|numeric|min:0','invoice_total'=>'required|numeric|min:0','ebupot_number'=>'nullable|string|max:255','ebupot_date'=>'nullable|date','status'=>'required|in:waiting_data,draft,issued,paid,sent,reported,cancelled','checklist'=>'nullable|array','requires_escalation'=>'nullable|boolean','escalation_reason'=>'nullable|string','notes'=>'nullable|string','ebupot_file'=>'nullable|file|max:10240|mimes:pdf','invoice_file'=>'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png','transfer_file'=>'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png']);
        if(in_array($data['status'],['issued','paid','sent','reported'],true) && !$record->ebupot_file && !$request->hasFile('ebupot_file')) return back()->withErrors(['ebupot_file'=>'PDF Bukti Potong Coretax wajib diunggah sebelum status BPPU terbit atau sesudahnya.'])->withInput();
        $data+=$this->service->calculate($data); $data['checklist']=array_fill_keys(EbupotVendorService::CHECKS,false);
        foreach($request->input('checklist',[]) as $key=>$value){if(in_array($key,EbupotVendorService::CHECKS,true))$data['checklist'][$key]=(bool)$value;}
        $data['requires_escalation']=$request->boolean('requires_escalation'); $data['updated_by']=$request->user()->id;
        foreach(['ebupot_file','invoice_file','transfer_file'] as $field){if($request->hasFile($field)){if($record->{$field})Storage::disk('local')->delete($record->{$field});$data[$field]=$request->file($field)->store('private/ebupot/'.$this->companyId().'/'.$record->id);}}
        if(in_array($data['status'],['paid','sent','reported'],true)&&!$record->paid_at)$data['paid_at']=now(); if(in_array($data['status'],['sent','reported'],true)&&!$record->sent_at)$data['sent_at']=now();
        $record->update($data); return back()->with('success','Pekerjaan e-Bupot tersimpan; nominal dihitung ulang oleh sistem.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data=$request->validate(['payment_deadline_day'=>'required|integer|min:1|max:28','report_deadline_day'=>'required|integer|min:1|max:28','wa_initial_template'=>'required|string','wa_amount_template'=>'required|string','wa_sent_template'=>'required|string','email_template'=>'required|string']);
        $this->service->settings($this->companyId())->update($data); return back()->with('success','Template dan deadline diperbarui.');
    }

    public function destroyVendor(EbupotVendor $vendor): RedirectResponse
    {
        $this->ownVendor($vendor);
        $vendor->records()->each(function(EbupotVendorRecord $record){foreach(['ebupot_file','invoice_file','transfer_file'] as $field)if($record->{$field})Storage::disk('local')->delete($record->{$field});$record->delete();});
        $vendor->delete(); return back()->with('success','Vendor dan pekerjaan bulanannya dihapus.');
    }

    public function whatsapp(Request $request, EbupotVendorRecord $record): RedirectResponse
    {
        $this->ownRecord($record); $record->load('vendor'); $kind=$request->validate(['kind'=>'required|in:initial,amount,sent'])['kind']; $settings=$this->service->settings($this->companyId());
        $template=$settings->{'wa_'.$kind.'_template'}; $phone=preg_replace('/\D+/','',(string)$record->vendor->whatsapp); if(str_starts_with($phone,'0'))$phone='62'.substr($phone,1);
        abort_if(!$phone,422,'Nomor WhatsApp vendor belum diisi.'); return redirect()->away('https://wa.me/'.$phone.'?text='.rawurlencode($this->service->message($template,$record)));
    }

    public function print(EbupotVendorRecord $record): View { $this->ownRecord($record); return view('finance.ebupot-vendors.print',['record'=>$record->load('vendor'),'settings'=>$this->service->settings($this->companyId()),'checks'=>EbupotVendorService::CHECKS]); }
    public function download(EbupotVendorRecord $record, string $type) { $this->ownRecord($record); abort_unless(in_array($type,['ebupot_file','invoice_file','transfer_file'],true)&&$record->{$type},404); return Storage::disk('local')->download($record->{$type}); }
}
