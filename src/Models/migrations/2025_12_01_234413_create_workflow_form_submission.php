<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowFormSubmission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_form_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_id');
            $table->integer('submission_id');
            $table->integer('work_flow_id');
            $table->integer('work_flow_step_id');
            $table->timestamps();
        });

        Schema::create('workflow_form_submission_outputs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workflow_form_submission_id');
            $table->integer('work_step_id');
            $table->text('name');
            $table->text('value');
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
        Schema::dropIfExists('workflow_form_submissions');
        Schema::dropIfExists('workflow_form_submission_outputs');
    }
}
