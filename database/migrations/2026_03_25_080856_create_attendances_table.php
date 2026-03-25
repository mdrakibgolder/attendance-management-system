<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->dateTime('check_in_at')->nullable()->index();
            $table->dateTime('check_out_at')->nullable()->index();
            $table->string('check_in_ip', 45)->nullable();
            $table->string('check_out_ip', 45)->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->enum('status', ['present', 'incomplete', 'absent', 'on_leave'])->default('present')->index();
            $table->boolean('is_late')->default(false)->index();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedInteger('total_work_minutes')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('manager_approved_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'attendance_date']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
