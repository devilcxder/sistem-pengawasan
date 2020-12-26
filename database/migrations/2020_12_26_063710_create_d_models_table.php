<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('d_models', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('model_name');
            $table->longText('model_desc')->nullable();
            $table->float('data_split');
            $table->float('accuracy');
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
        Schema::dropIfExists('d_models');
    }
}
