<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;

class Invoices_Report extends Controller
{
    function __construct()
    {
        
    $this->middleware('permission:تقرير الفواتير', ['only' => ['index']]);
    
    
    }
    public function index(){

        return view('reports.invoices_report');
           
       }
       public function Search_invoices(Request $request){

        $rdio = $request->rdio;
    
    
     // في حالة البحث بنوع الفاتورة
        
        if ($rdio == 1) {
                //في حالة جميع الفواتير
           
            if($request->type =='جميع الفواتير' && $request->start_at =='' && $request->end_at ==''){
                $invoices = Invoice::all();
                $type = $request->type;
                return view('reports.invoices_report',compact('type'))->withDetails($invoices);
            }

             //في حالة تحديد التاريخ مع جميع الفواتير
           
            if($request->type =='جميع الفواتير' && $request->start_at !='' && $request->end_at !=''){
                $start_at = date($request->start_at);
                $end_at = date($request->end_at);
                $type = $request->type;
                
                $invoices = Invoice::whereBetween('invoice_Date',[$start_at,$end_at])->get();
                return view('reports.invoices_report',compact('type','start_at','end_at'))->withDetails($invoices);
            }
     // في حالة عدم تحديد تاريخ
             if ($request->type && $request->start_at =='' && $request->end_at =='') {
                
               $invoices = Invoice::where('Status',$request->type)->get();
               $type = $request->type;
               return view('reports.invoices_report',compact('type'))->withDetails($invoices);
            }
        
             
            // في حالة تحديد تاريخ 

            if ($request->type && $request->start_at !='' && $request->end_at !='') {
               
              $start_at = date($request->start_at);
              $end_at = date($request->end_at);
              $type = $request->type;
              
              $invoices = Invoice::whereBetween('invoice_Date',[$start_at,$end_at])->where('Status','=',$request->type)->get();
              return view('reports.invoices_report',compact('type','start_at','end_at'))->withDetails($invoices);
              
            }
    
     else{
        return view('reports.invoices_report');
     }
            
        } 
        
    //====================================================================
        
    // في البحث برقم الفاتورة
        else {
            
            $invoices = Invoice::where('invoice_number',$request->invoice_number)->get();
            return view('reports.invoices_report')->withDetails($invoices);
            
        }
    
        
         
        }
}
