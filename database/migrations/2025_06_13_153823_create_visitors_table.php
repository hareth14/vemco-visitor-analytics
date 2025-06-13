<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitorsTable extends Migration
{
    public function up()
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('sensor_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('count');
            $table->timestamps();

            $table->unique(['location_id', 'sensor_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitors');
    }
}
