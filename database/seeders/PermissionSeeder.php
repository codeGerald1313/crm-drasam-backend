<?php

namespace Database\Seeders;

use App\Models\DescriptionPermission;
use App\Models\ListPermission;
use App\Models\RoleStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $role_admin = Role::create(['name' => 'admin']);
        RoleStatus::create(['status' => 1, 'id_role' => intval($role_admin->id)]);

        //Users
        Permission::create(['name' => 'users.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'users.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'users.edit'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'users.record'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'users.delete'])->syncRoles([$role_admin]);

        //Contacts
        Permission::create(['name' => 'contacts.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'contacts.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'contacts.edit'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'contacts.record'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'contacts.delete'])->syncRoles([$role_admin]);

        //Type Document
        Permission::create(['name' => 'type_document.list'])->syncRoles([$role_admin]);

        //Role
        Permission::create(['name' => 'role.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'role.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'role.edit'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'role.delete'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'role.destroy'])->syncRoles([$role_admin]);

        //Permission
        Permission::create(['name' => 'permission.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'permission.give_permissions'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'permission.revoke_permissions'])->syncRoles([$role_admin]);

        //Conexion
        Permission::create(['name' => 'conexion.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'conexion.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'conexion.edit'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'conexion.delete'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'conexion.destroy'])->syncRoles([$role_admin]);

        // Closure Reasons
        Permission::create(['name' => 'closure_reasons.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'closure_reasons.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'closure_reasons.update'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'closure_reasons.record'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'closure_reasons.delete'])->syncRoles([$role_admin]);

        // Quickly Answers
        Permission::create(['name' => 'quickly_answers.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'quickly_answers.create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'quickly_answers.delete'])->syncRoles([$role_admin]);

        // Chats
        Permission::create(['name' => 'data.records'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.conversations'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.messages'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.asign'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.contact_create'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.asign_chat'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.close_conversation'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.reasing_conversation'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.reminder'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data.add_tag'])->syncRoles([$role_admin]);

        // Informes
        Permission::create(['name' => 'reports.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'reports.new_conversations'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'reports.monitor_activities'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'reports.team_performance'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'reports.filter_by'])->syncRoles([$role_admin]);

        // Mensajes Masivos
        Permission::create(['name' => 'mass_messages.list'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'mass_messages.send_mass_messages'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'mass_messages.details_mass_messages'])->syncRoles([$role_admin]);

        // Vision General
        Permission::create(['name' => 'dashboard.list'])->syncRoles([$role_admin]);

        //Goblal permissions
        Permission::create(['name' => 'users'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'contacts'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'type_document'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'roles'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'permissions'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'conexion'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'closure_reasons'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'quickly_answers'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'data'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'reports'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'massmessages'])->syncRoles([$role_admin]);
        Permission::create(['name' => 'dashboard'])->syncRoles([$role_admin]);

        // Creación del administrador
        User::create([
            'document' => '00000000',
            'name' => 'admin',
            'last_name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'status' => 2,
            'admin' => 1,
            'hour_start' => '00:00:00',
            'hour_end' => '23:59:59'
        ])->assignRole('admin');

        // Creación del primer usuario (Oficina de Catastro)
        User::create([
            'document' => '11111111',
            'name' => 'Oficina de Catastro',
            'last_name' => 'Catastro',
            'email' => 'catastro@gmail.com',
            'password' => Hash::make('12345678'),
            'status' => 2,
            'admin' => 0,
            'hour_start' => '00:00:00',
            'hour_end' => '23:59:59'
        ])->assignRole('admin');

        // Creación del segundo usuario (Oficina de Titulación)
        User::create([
            'document' => '22222222',
            'name' => 'Oficina de Titulación',
            'last_name' => 'Titulación',
            'email' => 'titulacion@gmail.com',
            'password' => Hash::make('23456781'),
            'status' => 2,
            'admin' => 0,
            'hour_start' => '00:00:00',
            'hour_end' => '23:59:59'
        ])->assignRole('admin');

        // Creación del tercer usuario (Oficina de Saneamiento)
        User::create([
            'document' => '33333333',
            'name' => 'Oficina de Saneamiento',
            'last_name' => 'Saneamiento',
            'email' => 'saneamiento@gmail.com',
            'password' => Hash::make('34567812'),
            'status' => 2,
            'admin' => 0,
            'hour_start' => '00:00:00',
            'hour_end' => '23:59:59'
        ])->assignRole('admin');
    }
}
