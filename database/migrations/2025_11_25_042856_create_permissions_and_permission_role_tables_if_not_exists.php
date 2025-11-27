<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea las tablas permissions y permission_role solo si no existen
     * Estas son tablas internas del sistema para gestionar permisos
     */
    public function up(): void
    {
        // Crear tabla permissions si no existe
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Ej: 'usuarios.create', 'ventas.view', etc.
                $table->string('slug')->unique(); // Versión URL-friendly
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // Crear tabla permission_role si no existe
        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
                $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
                $table->timestamps();

                // Evitar duplicados
                $table->unique(['permission_id', 'role_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     * NO elimina las tablas para preservar los datos
     */
    public function down(): void
    {
        // No hacer nada para preservar los datos
        // Si necesitas eliminar, descomenta las siguientes líneas:
        // Schema::dropIfExists('permission_role');
        // Schema::dropIfExists('permissions');
    }
};
