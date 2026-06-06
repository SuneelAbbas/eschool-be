<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->latest()->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        $permissionGroups = Permission::getByGroup();
        return view('admin.roles.show', compact('role', 'permissionGroups'));
    }
}
