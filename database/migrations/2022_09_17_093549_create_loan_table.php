<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->float('amount')->nullable();
            $table->integer('period')->nullable();
            $table->float('principal_amount')->nullable();
            $table->integer('balance')->nullable();
            $table->integer('interest_rate')->default(10)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 for PANDING 1 for APPROVED');
            $table->integer('approved_by')->nullable();
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
        Schema::dropIfExists('loan');
    }
}
