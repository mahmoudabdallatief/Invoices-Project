<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     function __construct()
{
    
$this->middleware('auth');
$this->middleware('permission:صلاحيات المستخدمين', ['only' => ['index']]);
$this->middleware('permission:عرض صلاحية', ['only' => ['show']]);
$this->middleware('permission:اضافة صلاحية', ['only' => ['create']]);
$this->middleware('permission:تعديل صلاحية', ['only' => ['edit']]);


}
    public function index()
    {
        $roles = Role::orderBy('id','desc')->get();
        return view('roles.index',compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $permission = Permission::all();
return view('roles.create',compact('permission'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
            
            ]);

            if ($validator->fails()) {
                return redirect()->route('roles.create')->withErrors($validator)->withInput();
            }
        $role = Role::create(['name' => $request->input('name')]);
$role->syncPermissions($request->input('permission'));
return redirect()->route('roles.index')
->with('Add','Role created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
$rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
->where("role_has_permissions.role_id",$id)
->get();
return view('roles.show',compact('role','rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $role = Role::findOrFail($id);
$permission = Permission::all();
$rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
->all();
return view('roles.edit',compact('role','permission','rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all() ,[
            'name' => 'required|unique:roles,name,'.$id,
            'permission' => 'required',
            
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

        $role = Role::findOrFail($id);
$role->name = $request->input('name');
$role->save();
$role->syncPermissions($request->input('permission'));
return redirect()->route('roles.index')
->with('edit','Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Role::find($id)->delete();
        return redirect()->route('roles.index')->with('delete','Role deleted successfully');
    }
}
