<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->time('slot_start');
            $table->string('slot_label');
            $table->string('task_title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'report_date', 'slot_start']);
            $table->index(['report_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_entries');
    }
};
