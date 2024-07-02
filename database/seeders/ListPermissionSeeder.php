<?php

namespace Database\Seeders;

use App\Models\ListPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Users
        ListPermission::create(['id_module' => 1, 'id_permission' => 1]);
        ListPermission::create(['id_module' => 1, 'id_permission' => 2]);
        ListPermission::create(['id_module' => 1, 'id_permission' => 3]);
        ListPermission::create(['id_module' => 1, 'id_permission' => 4]);
        ListPermission::create(['id_module' => 1, 'id_permission' => 5]);

        //Contacts
        ListPermission::create(['id_module' => 2, 'id_permission' => 6]);
        ListPermission::create(['id_module' => 2, 'id_permission' => 7]);
        ListPermission::create(['id_module' => 2, 'id_permission' => 8]);
        ListPermission::create(['id_module' => 2, 'id_permission' => 9]);
        ListPermission::create(['id_module' => 2, 'id_permission' => 10]);

        //Type Document
        ListPermission::create(['id_module' => 3, 'id_permission' => 11]);
        
        //Role
        ListPermission::create(['id_module' => 4, 'id_permission' => 12]);
        ListPermission::create(['id_module' => 4, 'id_permission' => 13]);
        ListPermission::create(['id_module' => 4, 'id_permission' => 14]);
        ListPermission::create(['id_module' => 4, 'id_permission' => 15]);
        ListPermission::create(['id_module' => 4, 'id_permission' => 16]);

        //Permission
        ListPermission::create(['id_module' => 5, 'id_permission' => 17]);
        ListPermission::create(['id_module' => 5, 'id_permission' => 18]);
        ListPermission::create(['id_module' => 5, 'id_permission' => 19]);

        //Conexion
        ListPermission::create(['id_module' => 6, 'id_permission' => 20]);
        ListPermission::create(['id_module' => 6, 'id_permission' => 21]);
        ListPermission::create(['id_module' => 6, 'id_permission' => 22]);
        ListPermission::create(['id_module' => 6, 'id_permission' => 23]);
        ListPermission::create(['id_module' => 6, 'id_permission' => 24]);

        //Closure Reasons
        ListPermission::create(['id_module' => 7, 'id_permission' => 25]);
        ListPermission::create(['id_module' => 7, 'id_permission' => 26]);
        ListPermission::create(['id_module' => 7, 'id_permission' => 27]);
        ListPermission::create(['id_module' => 7, 'id_permission' => 28]);
        ListPermission::create(['id_module' => 7, 'id_permission' => 29]);

        //Quickly Answers
        ListPermission::create(['id_module' => 8, 'id_permission' => 30]);
        ListPermission::create(['id_module' => 8, 'id_permission' => 31]);
        ListPermission::create(['id_module' => 8, 'id_permission' => 32]);

        //Data
        ListPermission::create(['id_module' => 9, 'id_permission' => 33]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 34]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 35]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 36]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 37]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 38]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 39]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 40]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 41]);
        ListPermission::create(['id_module' => 9, 'id_permission' => 42]);

        // Informes
        ListPermission::create(['id_module' => 10, 'id_permission' => 43]);
        ListPermission::create(['id_module' => 10, 'id_permission' => 44]);
        ListPermission::create(['id_module' => 10, 'id_permission' => 45]);
        ListPermission::create(['id_module' => 10, 'id_permission' => 46]);
        ListPermission::create(['id_module' => 10, 'id_permission' => 47]);
        
        // Mensajes Masivos
        ListPermission::create(['id_module' => 11, 'id_permission' => 48]);
        ListPermission::create(['id_module' => 11, 'id_permission' => 49]);
        ListPermission::create(['id_module' => 11, 'id_permission' => 50]);

        // Vision General
        ListPermission::create(['id_module' => 12, 'id_permission' => 51]);

        //Gobal Permissions
        ListPermission::create(['id_module' => 13, 'id_permission' => 52]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 53]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 54]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 55]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 56]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 57]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 58]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 59]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 60]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 61]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 62]);
        ListPermission::create(['id_module' => 13, 'id_permission' => 63]);
    }
}
