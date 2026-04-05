<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_adjustment_id')->constrained();
            $table->foreignId('break_id')->constrained();
            $table->time('after_break_start_at')->nullable();
            $table->time('after_break_end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_adjustments');
    }
}
