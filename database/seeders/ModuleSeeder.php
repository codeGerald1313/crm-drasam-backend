<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ModulePermission::create(['name' => 'USUARIOS']);
        ModulePermission::create(['name' => 'CONTACTOS']);
        ModulePermission::create(['name' => 'TIPO DE DOCUMENTOS']);
        ModulePermission::create(['name' => 'ROLES']);
        ModulePermission::create(['name' => 'PERMISOS']);
        ModulePermission::create(['name' => 'CONEXION']);
        ModulePermission::create(['name' => 'RAZONES DE CIERRE']);
        ModulePermission::create(['name' => 'RESPUESTAS RAPIDAS']);
        ModulePermission::create(['name' => 'CHAT']);
        ModulePermission::create(['name' => 'INFORMES']);
        ModulePermission::create(['name' => 'MENSAJES MASIVOS']);
        ModulePermission::create(['name' => 'VISION GENERAL']);
        ModulePermission::create(['name' => 'PERMISOS GLOBALES']);
    }
}
