<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha')->useCurrent();
            $table->smallInteger('tipo')->comment('1=Contado, 2=Credito');
            $table->decimal('total', 10, 2)->nullable();
            $table->smallInteger('estado')->default(0);
            $table->timestamps();
        });

        // Agregar restricción CHECK para tipo (solo permite 1 o 2)
        DB::statement('ALTER TABLE ventas ADD CONSTRAINT ventas_tipo_check CHECK (tipo IN (1, 2))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS ventas_tipo_check');
        Schema::dropIfExists('ventas');
    }
};
