<?php

use Illuminate\Support\Facades\Route;
use App\http\Controllers\HomeController;
use App\http\Controllers\InvoiceController;
use App\http\Controllers\SectionController;
use App\http\Controllers\ProductController;
use App\http\Controllers\InvoiceDetailController;
use App\http\Controllers\InvoiceAttachmentController;
use App\http\Controllers\UserController;
use App\http\Controllers\RoleController;
use App\http\Controllers\Invoices_Report;
use App\http\Controllers\Customers_Report;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
})->name('index');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('/invoices',InvoiceController::class);
Route::resource('/sections',SectionController::class);
Route::resource('/products',ProductController::class);
Route::resource('/attachments',InvoiceAttachmentController::class);
Route::post('/section',[InvoiceController::class, 'getproducts']);
Route::post('/add_attachment',[InvoiceAttachmentController::class, 'store'])->name('add_attachment');
Route::get('/invoices_details/{id}',[InvoiceDetailController::class,'invoices_details'])->name('invoices_details');
Route::get('/download/{number}/{file}',[InvoiceDetailController::class,'download'])->name('download');

Route::get('/view/{number}/{file}',[InvoiceDetailController::class,'view'])->name('view');
Route::post('/Status_Update',[InvoiceController::class,'Status_Update'])->name('Status_Update');

Route::get('/partial',[InvoiceController::class,'partial'])->name('partial');
Route::get('/paid',[InvoiceController::class,'paid'])->name('paid');
Route::get('/unpaid',[InvoiceController::class,'unpaid'])->name('unpaid');
Route::get('/print/{id}',[InvoiceController::class,'print'])->name('print');
Route::get('/exportinvoice', [InvoiceController::class, 'export'])->name('exportinvoice');
Route::get('/archive',[InvoiceController::class,'archive'])->name('archive');
Route::post('/restore',[InvoiceController::class,'restore'])->name('restore');


Route::group(['middleware' => ['auth']], function() {

    Route::resource('/roles',RoleController::class);

    Route::resource('/users',UserController::class);
    
    });
    
    Route::get('/invoices_report',[Invoices_Report::class,'index'])->name('invoices_report');
    Route::post('/Search_invoices',[Invoices_Report::class,'Search_invoices'])->name('Search_invoices');
    Route::get('/customers_report',[Customers_Report::class,'index'])->name('customers_report');
  
    Route::post('/Search_customers',[Customers_Report::class,'Search_customers'])->name('Search_customers');
    
    Route::get('/MarkAsRead_all',[InvoiceController::class,'MarkAsRead_all'])->name('MarkAsRead_all');
    


