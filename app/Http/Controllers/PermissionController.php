<?php

namespace App\Http\Controllers;

use App\Models\DescriptionPermission;
use App\Models\ListPermission;
use App\Models\ModulePermission;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $list_permissions = ListPermission::get();
        $grouped_data = [];

        foreach ($list_permissions as $row) {
            $module = ModulePermission::find($row->id_module);
            $permission = Permission::find($row->id_permission);
            $description = DescriptionPermission::find($row->id);

            $grouped_data[$module->name][] = [
                'id' => $row->id_permission,
                'permission' => $permission->name,
                'description' =>  $description->description
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $grouped_data
        ], Response::HTTP_OK);
    }

    public function permissionAll($id)
    {

        $role = Role::find($id);

        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    }

    // give permissions to users
    public function givePermissions(Request $request, $id)
    {
        try {
            $user = User::find($id);

            foreach ($request->permissions as $row) {
                $permission = Permission::find($row);
                $user->givePermissionTo($permission);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'aggregator permissions to user',
                'user' => $user
            ]);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'there was an error when giving permissions',
                'error' => $error->getMessage()
            ]);
        }
    }

    public function revokePermissions(Request $request, $id)
    {
        try {
            $user = User::find($id);

            foreach ($request->permissions as $row) {
                $permission = Permission::find($row);
                $user->revokePermissionTo($permission);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'permissions removed from user',
                'user' => $user
            ]);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'there was an error when removing the permissions',
                'error' => $error->getMessage()
            ]);
        }
    }
}
