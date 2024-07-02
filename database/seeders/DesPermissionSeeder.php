<?php

namespace Database\Seeders;

use App\Models\DescriptionPermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Users
        DescriptionPermission::create(['description' => 'Ver Usuarios', 'id_permission' => 1]);
        DescriptionPermission::create(['description' => 'Crear Usuario', 'id_permission' => 2]);
        DescriptionPermission::create(['description' => 'Editar Usuario', 'id_permission' => 3]);
        DescriptionPermission::create(['description' => 'Buscar Usuario', 'id_permission' => 4]);
        DescriptionPermission::create(['description' => 'Eliminar Usuario', 'id_permission' => 5]);

        //Contacts
        DescriptionPermission::create(['description' => 'Ver Contacto', 'id_permission' => 6]);
        DescriptionPermission::create(['description' => 'Crear Contacto', 'id_permission' => 7]);
        DescriptionPermission::create(['description' => 'Editar Contacto', 'id_permission' => 8]);
        DescriptionPermission::create(['description' => 'Buscar Contacto', 'id_permission' => 9]);
        DescriptionPermission::create(['description' => 'Eliminar Contacto', 'id_permission' => 10]);

        //Type Document
        DescriptionPermission::create(['description' => 'Ver Tipo De Documento', 'id_permission' => 11]);

        //Role
        DescriptionPermission::create(['description' => 'Ver Roles', 'id_permission' => 12]);
        DescriptionPermission::create(['description' => 'Crear Rol', 'id_permission' => 13]);
        DescriptionPermission::create(['description' => 'Editar Rol', 'id_permission' => 14]);
        DescriptionPermission::create(['description' => 'Eliminar Rol', 'id_permission' => 15]);
        DescriptionPermission::create(['description' => 'Destruir Rol', 'id_permission' => 16]);

        //Permission
        DescriptionPermission::create(['description' => 'Ver Permisos', 'id_permission' => 17]);
        DescriptionPermission::create(['description' => 'Dar Permisos', 'id_permission' => 18]);
        DescriptionPermission::create(['description' => 'Quitar Permisos', 'id_permission' => 19]);

        //Conexion
        DescriptionPermission::create(['description' => 'Ver conexion', 'id_permission' => 20]);
        DescriptionPermission::create(['description' => 'Crear conexion', 'id_permission' => 21]);
        DescriptionPermission::create(['description' => 'Editar conexion', 'id_permission' => 22]);
        DescriptionPermission::create(['description' => 'Eliminar conexion', 'id_permission' => 23]);
        DescriptionPermission::create(['description' => 'Destruir conexion', 'id_permission' => 24]);

        //Closure Reasons
        DescriptionPermission::create(['description' => 'Ver razones de cierre', 'id_permission' => 25]);
        DescriptionPermission::create(['description' => 'Crear razones de cierre', 'id_permission' => 26]);
        DescriptionPermission::create(['description' => 'Editar razones de cierre', 'id_permission' => 27]);
        DescriptionPermission::create(['description' => 'Record razones de cierre', 'id_permission' => 28]);
        DescriptionPermission::create(['description' => 'Eliminar razones de cierre', 'id_permission' => 29]);

        //Quickly Answers
        DescriptionPermission::create(['description' => 'Ver respuestas rapidas', 'id_permission' => 30]);
        DescriptionPermission::create(['description' => 'Crear respuestas rapidas', 'id_permission' => 31]);
        DescriptionPermission::create(['description' => 'Eliminar respuestas rapidas', 'id_permission' => 32]);

        //Data
        DescriptionPermission::create(['description' => 'Records', 'id_permission' => 33]);
        DescriptionPermission::create(['description' => 'Conversaciones', 'id_permission' => 34]);
        DescriptionPermission::create(['description' => 'Mensajes', 'id_permission' => 35]);
        DescriptionPermission::create(['description' => 'Asignaciones', 'id_permission' => 36]);
        DescriptionPermission::create(['description' => 'Crear contacto', 'id_permission' => 37]);
        DescriptionPermission::create(['description' => 'Asignar chat', 'id_permission' => 38]);
        DescriptionPermission::create(['description' => 'Cerrar conversación', 'id_permission' => 39]);
        DescriptionPermission::create(['description' => 'Reasignar conversación', 'id_permission' => 40]);
        DescriptionPermission::create(['description' => 'Recordar', 'id_permission' => 41]);
        DescriptionPermission::create(['description' => 'Añadir Etiqueta', 'id_permission' => 42]);

        // Informes
        DescriptionPermission::create(['description' => 'Ver informres', 'id_permission' => 43]);
        DescriptionPermission::create(['description' => 'Nuevas Conversaciones', 'id_permission' => 44]);
        DescriptionPermission::create(['description' => 'Monitoriar actividades', 'id_permission' => 45]);
        DescriptionPermission::create(['description' => 'Rendimiento del equipo ', 'id_permission' => 46]);
        DescriptionPermission::create(['description' => 'Filtrar', 'id_permission' => 47]);

        // Mensajes Masivos
        DescriptionPermission::create(['description' => 'Ver', 'id_permission' => 48]);
        DescriptionPermission::create(['description' => 'Enviar Mensajes', 'id_permission' => 49]);
        DescriptionPermission::create(['description' => 'Ver Detalle', 'id_permission' => 50]);

        // Vision General
        DescriptionPermission::create(['description' => 'Ver', 'id_permission' => 51]);

        // Permisos Globales
        DescriptionPermission::create(['description' => 'Equipo', 'id_permission' => 52]);
        DescriptionPermission::create(['description' => 'Contacto', 'id_permission' => 53]);
        DescriptionPermission::create(['description' => 'Tipo de documentos', 'id_permission' => 54]);
        DescriptionPermission::create(['description' => 'Roles', 'id_permission' => 55]);
        DescriptionPermission::create(['description' => 'Permisos', 'id_permission' => 56]);
        DescriptionPermission::create(['description' => 'Conexiones', 'id_permission' => 57]);
        DescriptionPermission::create(['description' => 'Razones de cierre', 'id_permission' => 58]);
        DescriptionPermission::create(['description' => 'Respuestas Rapidas', 'id_permission' => 59]);
        DescriptionPermission::create(['description' => 'Chat', 'id_permission' => 60]);
        DescriptionPermission::create(['description' => 'Informes', 'id_permission' => 61]);
        DescriptionPermission::create(['description' => 'Mensajes Masivos', 'id_permission' => 62]);
        DescriptionPermission::create(['description' => 'Visión General', 'id_permission' => 63]);
    }
}
