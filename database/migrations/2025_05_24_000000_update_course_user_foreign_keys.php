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
        Schema::table('course_user', function (Blueprint $table) {
            // حذف القيد الحالي
            $table->dropForeign(['course_id']);
            
            // إضافة القيد الجديد مع cascade
            $table->foreign('course_id')
                  ->references('id')
                  ->on('courses')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_user', function (Blueprint $table) {
            // حذف القيد المعدل
            $table->dropForeign(['course_id']);
            
            // إعادة القيد الأصلي
            $table->foreign('course_id')
                  ->references('id')
                  ->on('courses');
        });
    }
};