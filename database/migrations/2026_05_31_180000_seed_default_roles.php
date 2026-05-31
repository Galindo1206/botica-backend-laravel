<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'description' => 'Acceso completo al panel administrativo.',
            ],
            [
                'name' => 'Cliente',
                'description' => 'Cuenta para clientes de la botica.',
            ],
            [
                'name' => 'Trabajador',
                'description' => 'Acceso operativo para personal interno.',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'description' => $role['description'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereIn('name', ['Administrador', 'Cliente', 'Trabajador'])
            ->delete();
    }
};
