<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $teacher = Role::create(['name' => 'teacher']);
        $tutor = Role::create(['name' => 'tutor']);


        $adminPermission = Permission::create(['name' => 'Ver, editar y gestionar alumnos y tutores']);
        $teacherPermission = Permission::create(['name' => 'Ver, editar y gestionar alumnos']);
        $tutorPermission = Permission::create(['name' => 'Ver a un alumno']);

        $admin->givePermissionTo($adminPermission);
        $adminPermission->assignRole($admin);

        $teacher->givePermissionTo($teacherPermission);
        $teacherPermission->assignRole($teacher);

        $tutor->givePermissionTo($tutorPermission);
        $tutorPermission->assignRole($tutor);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // // Primero, elimina las restricciones de clave externa en las otras tablas
        // Schema::table('model_has_roles', function (Blueprint $table) {
        //     $table->dropColumn(['role_id']);
        // });
        // Schema::table('role_has_permissions', function (Blueprint $table) {
        //     $table->dropForeign(['permission_id']);
        //     $table->dropForeign(['role_id']);
        // });

        // // Luego, puedes eliminar la tabla `roles`
        // Schema::dropIfExists('roles');
    }
};
