<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Section;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{
    function __construct()
{
    
$this->middleware('permission:المنتجات', ['only' => ['index']]);


}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = Section::all();
        $products = Product::with('section')->orderBy('id','desc')->get();
        return view('products.products', compact('sections','products'));
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
        $validator =Validator::make($request->all(),[
            'Product_name' => 'required|unique:products|max:255',
            'description' => 'required',
            'section_id' => 'required',

        ],[

            'Product_name.required' =>'يرجي ادخال اسم المنتج',
            'Product_name.unique' =>'اسم المنتج مسجل مسبقا',
            'description.required' =>'يرجي ادخال البيان',
            'section_id.required' =>'يرجي ادخال القسم',

        ]);
        if ($validator->fails()) {
            return redirect()->route('products.index')->withErrors($validator)->withInput();
        }
        Product::insert([
            'Product_name' => $request->Product_name,
            'description' => $request->description,
           'section_id' => $request->section_id

        ]);
        return redirect()->route('products.index')->with('Add','تم اضافة المنتج بنجاح ');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator =Validator::make($request->all(),[
            'Product_name' => 'required|max:255|unique:products,Product_name,'.$id,
            'description' => 'required',
            'section_id' => 'required',
        ],[

            'Product_name.required' =>'يرجي ادخال اسم المنتج',
            'Product_name.unique' =>'اسم المنتج مسجل مسبقا',
            'description.required' =>'يرجي ادخال البيان',
            'section_id.required' =>'يرجي ادخال القسم',

        ]);
        if ($validator->fails()) {
            return redirect()->route('products.index')->withErrors($validator)->withInput();
        }
        Product::where('id',$id)->update([
            'Product_name' => $request->Product_name,
            'description' => $request->description,
           'section_id' => $request->section_id

        ]);
        return redirect()->route('products.index')->with('Edit','تم تعديل المنتج بنجاح ');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('products.index')->with('Delete','تم حذف المنتج بنجاح ');
    }
}
