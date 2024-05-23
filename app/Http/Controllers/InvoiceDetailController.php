<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Support\Facades\Storage;
use App\Models\InvoiceAttachment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class InvoiceDetailController extends Controller
{
    function __construct()
    {
        
    $this->middleware('auth');
    $this->middleware('permission:الاشعارات', ['only' => ['invoices_details']]);
    
    
    
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function show(InvoiceDetail $invoiceDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceDetail $invoiceDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoiceDetail $invoiceDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceDetail  $invoiceDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceDetail $invoiceDetail)
    {
        //
    }
    public function invoices_details($id){

        $invoices = Invoice::findorfail($id);
        $details  = InvoiceDetail::where('id_Invoice',$id)->get();
        $attachments  = InvoiceAttachment::where('invoice_id',$id)->get();

      
   $notification = DB::table('notifications')
   ->where('notifiable_id', Auth::user()->id)
   ->where('data->id', $id)
   ->first();
     if( $notification && $notification->read_at === NULL){
        DB::table('notifications')
        ->where('notifiable_id', Auth::user()->id)
        ->where('data->id', $id)
        ->update([
            'read_at' => now(),
        ]);
     }
       
  
            
        


        return view('invoices.details_invoice',compact('invoices','details','attachments'));
    }

    public function download($number,$file)

    {
        $contents= Storage::disk('public_uploads')->getDriver()->getAdapter()->applyPathPrefix($number.'/'.$file);
        return response()->download( $contents);
    }



    public function view($number, $file)
    {
        $filePath = Storage::disk('public_uploads')->path($number . '/' . $file);
    
        if (!file_exists($filePath)) {
            abort(404);
        }
    
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    
        if (in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])) {
            $fileContents = file_get_contents($filePath);
            $base64File = base64_encode($fileContents);
    
            return view('pdf', compact('base64File', 'fileExtension'));
        } else {
            return response()->file($filePath);
        }
    }
}
