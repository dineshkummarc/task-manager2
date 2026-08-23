<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('projects')->insert([
            ['name' => 'BPOVO', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'GITMIT', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
        });

        $bpovoId = DB::table('projects')->where('name', 'BPOVO')->value('id');

        DB::table('departments')->whereNull('project_id')->update(['project_id' => $bpovoId]);
        DB::table('users')->whereNull('project_id')->update(['project_id' => $bpovoId]);
        DB::table('tasks')->whereNull('project_id')->update(['project_id' => $bpovoId]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('projects');
    }
};
