<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Http\Resources\AdminResource;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    protected $fillable = ['name', 'email', 'password'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AdminResource::collection(Admin::paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $admin = new Admin();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->image = $request->image;
        $admin->bio = $request->bio;
        $admin->save();

        return new AdminResource($admin);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $admin = Admin::findOrFail($id);
        return new AdminResource($admin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->name = $request->name;
        $admin->email = $request->email;
        if ($request->has('password')) {
            $admin->password = Hash::make($request->password);
        }
        $admin->image = $request->image;
        $admin->bio = $request->bio;
        $admin->save();

        return new AdminResource($admin);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
