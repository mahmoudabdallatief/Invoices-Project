<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AddInvoice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Section;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceAttachment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\InvoiceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\Add_invoice_new;
use Carbon\Carbon;


class InvoiceController extends Controller
{

    function __construct()
    {
    
    $this->middleware('permission:قائمة الفواتير', ['only' => ['index']]);
    $this->middleware('permission:الفواتير المدفوعة جزئيا', ['only' => ['partial']]);
    $this->middleware('permission:الفواتير المدفوعة', ['only' => ['paid']]);
    $this->middleware('permission:الفواتير الغير مدفوعة', ['only' => ['unpaid']]);
    $this->middleware('permission:اضافة فاتورة', ['only' => ['create']]);
    $this->middleware('permission:تعديل فاتورة', ['only' => ['edit']]);
    $this->middleware('permission:تغير حالة الدفع', ['only' => ['Status_Update']]);
    $this->middleware('permission:تصدير EXCEL', ['only' => ['export']]);
    $this->middleware('permission:ارشيف الفواتير', ['only' => ['archive']]);
    $this->middleware('permission:طباعةالفاتورة', ['only' => ['print']]);
    $this->middleware('permission:الاشعارات', ['only' => ['MarkAsRead_all']]);
    
    
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
 
    public function index()
    {
        
        $invoices = Invoice::orderBy('id', 'DESC')->get();
     

        return view('invoices.invoices', compact('invoices'));
         
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections = Section::all();
        return view('invoices.add_invoice', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|integer|unique:invoices',
            'invoice_Date' => 'required',
            'Due_date' => 'required',
            'product' => 'required',
            'Section' => 'required',
            'Amount_collection' => 'required',
            'Amount_Commission' => 'required',
            'Discount' => 'required',
            'Value_VAT' => 'required',
            'Rate_VAT' => 'required',
            'Total' => 'required',
        ],[

            'invoice_number.required' =>'يرجي ادخال رقم الفاتورة ',
            'invoice_Date.required' =>'يرجي ادخال تاريخ الفاتورة ',
            'Due_date.required' =>'يرجي ادخال  تاريخ الإستحقاق',
            'product.required' =>'يرجي ادخال اسم المنتج',
            'Section.required' =>'يرجي ادخال اسم القسم',
            'Amount_collection.required' =>'يرجي ادخال مبلغ التحصيل ',
            'Amount_Commission.required' =>'يرجي ادخال مبلغ العمولة ',
            'Discount.required' =>'يرجي ادخال مبلغ الخصم ',
            'Rate_VAT.required' =>'يرجي ادخال نسبة ضريبة القيمة المضافة ',
            'Value_VAT.required' =>'يرجي ادخال قيمة ضريبة القيمة المضافة ',
            'Total.required' =>'يرجي ادخال  الاجمالي شامل الضريبة',
            'invoice_number.integer' =>'يجب أن يكون رقم الفاتورة عدداً صحيحاً',
            'invoice_number.unique' =>'   رقم الفاتورة مسجل مسبقاً',
            

        ]);
        if ($validator->fails()) {
            return redirect()->route('invoices.create')->withErrors($validator)->withInput();
        }
        
            $invoice = Invoice::create([
                'invoice_number' => $request->invoice_number,
                'invoice_Date' => $request->invoice_Date,
                'Due_date' => $request->Due_date,
                'product' => $request->product,
                'section_id' => $request->Section,
                'Amount_collection' => $request->Amount_collection,
                'Amount_Commission' => $request->Amount_Commission,
                'Discount' => $request->Discount,
                'Value_VAT' => $request->Value_VAT,
                'Rate_VAT' => $request->Rate_VAT,
                'Total' => $request->Total,
                'Status' => 'غير مدفوعة',
                'Value_Status' => 2,
                'note' => $request->note,
            ]);
        
            $invoice_id = $invoice->id;

           
       
      
        InvoiceDetail::create([
            'id_Invoice' => $invoice_id,
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
            'note' => $request->note,
            'user' => Auth::user()->name,
        ]);

        if ($request->hasFile('pic')) {
            $image = $request->file('pic');
            $file_name = $image->getClientOriginalName();
            $invoice_number = $request->invoice_number;

            $attachments = new InvoiceAttachment();
            $attachments->file_name = $file_name;
            $attachments->invoice_number = $invoice_number;
            $attachments->Created_by = Auth::user()->name;
            $attachments->invoice_id = $invoice_id;
            $attachments->save();

            // move pic
            $imageName = $request->pic->getClientOriginalName();
            $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
        }
        //  $user = User::first();
        //    Notification::send($user, new AddInvoice($invoice_id));
        $user = User::all();
            
        Notification::send($user, new Add_invoice_new($invoice_id));
        return redirect()->route('invoices.index')->with('Add','تم اضافة الفاتورة بنجاح ');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoices = Invoice::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoices = Invoice::findorfail($id);
        $sections = Section::where('id','<>', $invoices->section_id)->get();
        return view('invoices.edit_invoice', compact('sections', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $invoices = Invoice::find($id);

        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|integer|unique:invoices,invoice_number,'.$id,
            'invoice_Date' => 'required',
            'Due_date' => 'required',
            'product' => 'required',
            'Section' => 'required',
            'Amount_collection' => 'required',
            'Amount_Commission' => 'required',
            'Discount' => 'required',
            'Value_VAT' => 'required',
            'Rate_VAT' => 'required',
            'Total' => 'required',
        ],[

            'invoice_number.required' =>'يرجي ادخال رقم الفاتورة ',
            'invoice_Date.required' =>'يرجي ادخال تاريخ الفاتورة ',
            'Due_date.required' =>'يرجي ادخال  تاريخ الإستحقاق',
            'product.required' =>'يرجي ادخال اسم المنتج',
            'Section.required' =>'يرجي ادخال اسم القسم',
            'Amount_collection.required' =>'يرجي ادخال مبلغ التحصيل ',
            'Amount_Commission.required' =>'يرجي ادخال مبلغ العمولة ',
            'Discount.required' =>'يرجي ادخال مبلغ الخصم ',
            'Rate_VAT.required' =>'يرجي ادخال نسبة ضريبة القيمة المضافة ',
            'Value_VAT.required' =>'يرجي ادخال قيمة ضريبة القيمة المضافة ',
            'Total.required' =>'يرجي ادخال  الاجمالي شامل الضريبة',
            'invoice_number.integer' =>'يجب أن يكون رقم الفاتورة عدداً صحيحاً',
            'invoice_number.unique' =>'   رقم الفاتورة مسجل مسبقاً',
            

        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        Invoice::where('id',$id)->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
        ]);
        InvoiceDetail::where('id_Invoice',$id)->update([
            'invoice_number' => $request->invoice_number,
            'product' => $request->product,
            'Section' => $request->Section,
            'note' => $request->note,
        ]);
        InvoiceAttachment::where('invoice_id',$id)->update([
            'invoice_number' => $request->invoice_number,
        ]);
       
        
        // dd($newInvoiceNumber,$oldInvoiceNumber);
    
// Get the old invoice number.
$oldDirPath = public_path('Attachments'. '/'.$invoices->invoice_number) ;

// Get the new invoice number.
$newDirPath = public_path('Attachments'. '/'.$request->invoice_number) ;

// Check if the old invoice number directory exists.
if (is_dir($oldDirPath)) {
    // Check if the new invoice number directory already exists.
    
        // Rename the old invoice number directory to the new invoice number directory.
        rename($oldDirPath, $newDirPath);
    
}


      
        return redirect()->route('invoices.index')->with('Edit','تم تعديل الفاتورة بنجاح ');
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
       
        
        $attachments = InvoiceAttachment::where('invoice_id', $id)->first();


        $id_page =$request->id_page;


        if ($id_page!=2) {
        if ($attachments) {

            Storage::disk('public_uploads')->deleteDirectory($attachments->invoice_number);
        }

        InvoiceAttachment::where('invoice_id', $id)->forceDelete();
        Invoice::where('id', $id)->forceDelete();
        InvoiceDetail::where('id_Invoice', $id)->forceDelete();
        
        return redirect()->route('invoices.index')->with('Delete','تم حذف الفاتورة بنجاح ');
    }
    else{
        InvoiceAttachment::where('invoice_id', $id)->delete();
        Invoice::where('id', $id)->delete();
        InvoiceDetail::where('id_Invoice', $id)->delete();
        return redirect()->route('archive')->with('archive_invoice','');
    }
        
    }

    public function Status_Update( Request $request)
    {
        $validator =Validator::make($request->all(),[
            
            'Status' => 'required',
            'Payment_Date' => 'required',
        ],[

            
            'Status.required' =>'يرجي  تعديل حالة الدفع',
            'Payment_Date.required' =>'يرجي ادخال تاريخ الدفع',

        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $invoices = Invoice::findOrFail($request->id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            InvoiceDetail::create([
                'id_Invoice' => $request->id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => Auth::user()->name,
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            InvoiceDetail::create([
                'id_Invoice' => $request->id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Section' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => Auth::user()->name,
            ]);
        }
        
         return redirect()->route('invoices.index')->with('Status_Update',"تم تحديث حالة الدفع بنجاح");

    }

    public function paid()
    {
        $invoices = Invoice::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }

    public function unpaid()
    {
        $invoices = Invoice::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

    public function partial()
    {
        $invoices = Invoice::where('Value_Status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }

    public function archive(){
        $invoices = Invoice::onlyTrashed()->get();

    
            return view('invoices.Archive_Invoices', compact('invoices'));
        

    }
    public function print($id)
    {
        $invoices = Invoice::where('id', $id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }
    public function restore(Request $request){
        $id = $request->invoice_id;
         $flight = Invoice::withTrashed()->where('id', $id)->restore();
         $flight = InvoiceDetail::withTrashed()->where('id_Invoice', $id)->restore();
         $flight = InvoiceAttachment::withTrashed()->where('invoice_id', $id)->restore();
         return redirect()->route('invoices.index')->with('restore_invoice',"");
    }

    public function export() 
    {
        return Excel::download(new InvoiceExport, 'الفواتير.xlsx');
    }

    public function getproducts(Request $request)
    {
        $products = DB::table("products")->where("section_id", $request->id)->get();
        return json_encode($products);
    }

    public function MarkAsRead_all ()
    {

        DB::table('notifications')->where('read_at',NULL)->where('notifiable_id', Auth::user()->id)->update([
            'read_at' =>now(),
        ]);

       
            return back();
        


    }
}
