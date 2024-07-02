<?php

namespace App\Http\Controllers;

use App\Http\Resources\User;
use App\Models\DescriptionPermission;
use App\Models\ListPermission;
use App\Models\ModulePermission;
use App\Models\RoleStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $list_permissions = Role::where('id', '<>', 1)->get();

        $data = Collection::make($list_permissions)->map(function ($row) {
            $status = RoleStatus::where('id_role', $row->id)->get();

            return [
                'id' => $row->id,
                'name' => $row->name,
                'status' => $status[0]->status
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {

        try {
            $this->validate($request, [
                'name' => 'required',
                'permissions' => 'required'
            ]);

            $role = Role::create(['name' => $request->input('name')]);

            foreach ($request->permissions as $row) {
                $permission = Permission::find($row);
                $role->givePermissionTo($permission);
            }

            RoleStatus::create(['status' => 1, 'id_role' => intval($role->id)]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registro exitoso',
                'role' => $role
            ]);
        } catch (Exception $error) {
            Log::error("Error al guardar el rol y permisos: " . $error->getMessage());

            return response()->json([
                'status' => 'error',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'permissions' => 'required'
            ]);

            $role = Role::find($id);
            $role->update(['name' => $request->name]);
            $role->permissions->pluck('name');

            foreach ($role->permissions as $row) {
                $role->revokePermissionTo($row->name);
            }

            foreach ($request->permissions as $row) {
                $permission = Permission::find($row);
                $role->givePermissionTo($permission);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'record updated',
                'role' => $role
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'failed to update',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function update_destroy($id)
    {
        try {
            RoleStatus::where('id_role', $id)
                ->update(['status' => 0]);

            return response()->json([
                'status' => 'success',
                'message' => 'role removed',
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'role removed'
        ]);
    }

    public function querypermisos($id)
    {
        $rolepermissions = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $id)->get();
        return response()->json(['data' => $rolepermissions]);
    }

    public function sendActiveStatus($id)
    {
        try {
            RoleStatus::where('id_role', $id)
                ->update(['status' => 1]);

            return response()->json([
                'status' => 'success',
                'message' => 'Role activated',
            ], Response::HTTP_OK);
        } catch (Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al activar',
                'error' => $error->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
