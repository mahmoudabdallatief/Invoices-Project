<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    function __construct()
{
    
$this->middleware('auth');
$this->middleware('permission:الاقسام', ['only' => ['index']]);


}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sections = Section::all();
        return view('sections.sections',compact('sections'));
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
            'section_name' => 'required|unique:sections|max:255',
            'description' => 'required',
        ],[

            'section_name.required' =>'يرجي ادخال اسم القسم',
            'section_name.unique' =>'اسم القسم مسجل مسبقا',
            'description.required' =>'يرجي ادخال البيان',

        ]);
        if ($validator->fails()) {
            return redirect()->route('sections.index')->withErrors($validator)->withInput();
        }
        Section::insert([
            'section_name' => $request->section_name,
            'description' => $request->description,
            'Created_by' => Auth::user()->name,

        ]);
        return redirect()->route('sections.index')->with('Add','تم اضافة القسم بنجاح ');

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator =Validator::make($request->all(),[
            'section_name' => 'required|max:255|unique:sections,section_name,'.$id,
            'description' => 'required',
        ],[

            'section_name.required' =>'يرجي ادخال اسم القسم',
            'section_name.unique' =>'اسم القسم مسجل مسبقا',
            'description.required' =>'يرجي ادخال البيان',

        ]);
        if ($validator->fails()) {
            return redirect()->route('sections.index')->withErrors($validator)->withInput();
        }
        Section::where('id',$id)->update([
            'section_name' => $request->section_name,
            'description' => $request->description,

        ]);
        return redirect()->route('sections.index')->with('Edit','تم تعديل القسم بنجاح ');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Section::findOrFail($id)->delete();
        return redirect()->route('sections.index')->with('Delete','تم حذف القسم بنجاح ');
    }
}
