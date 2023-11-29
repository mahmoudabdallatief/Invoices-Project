<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    function __construct()
{
    
$this->middleware('permission:قائمة المستخدمين', ['only' => ['index']]);
$this->middleware('permission:اضافة مستخدم', ['only' => ['create']]);
$this->middleware('permission:تعديل مستخدم', ['only' => ['edit']]);

}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::where('id','<>',Auth::user()->id)->orderBy('id','desc')->get();
        return view('users.show_users',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name','name')->all();

return view('users.Add_user',compact('roles'));
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'Status' => 'required',
            'roles_name' => 'required',
            

            ]);
            if ($validator->fails()) {
                return redirect()->route('users.create')->withErrors($validator)->withInput();
            }
          
          $user =  User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'Status' => $request->Status,
                'roles_name' => $request->roles_name,
    
            ]);
            $user->assignRole($request->input('roles_name'));
            return redirect()->route('users.index')
            ->with('success','تم اضافة المستخدم بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
            $user = User::findOrFail($id);

            $roles = Role::pluck('name','name')->all();

            $userRole = $user->roles->pluck('name','name')->all();

            return view('users.edit',compact('user','roles','userRole'));
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
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'Status' => 'required',
            'roles_name' => 'required',
            

            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if(!empty($request->password)){
                $password = Hash::make($request->password);
                User::where('id',$id)->update([
                     'password'=> $password
                ]);
            }
            User::where('id',$id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'Status' => $request->Status,
                'roles_name' => $request->roles_name,
    
            ]);
DB::table('model_has_roles')->where('model_id',$id)->delete();

$user = User::find($id); // Retrieve the updated user model
$user->assignRole($request->input('roles_name'));
return redirect()->route('users.index')

->with('success','تم تحديث معلومات المستخدم بنجاح');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
return redirect()->route('users.index')->with('success','تم حذف المستخدم بنجاح');
    }
}
