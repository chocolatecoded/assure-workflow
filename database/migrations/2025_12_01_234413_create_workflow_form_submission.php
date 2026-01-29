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
        // store the sub
        Schema::create('workflow_form_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_order_id');
            $table->integer('technician_id');
            $table->integer('work_flow_id');
            $table->integer('work_flow_step_id');
            $table->string('work_order_status')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_form_submissions_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workflow_form_submission_id');
            $table->string('submission_id');
            $table->string('form_id');
            $table->integer('work_order_id');
            $table->integer('work_flow_step_id');
            $table->timestamps();
        });

        Schema::create('workflow_form_submission_outputs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('workflow_form_submissions_steps_id');
            $table->integer('condition_id')->nullable();
            $table->text('name');
            $table->text('value');
            $table->timestamps();
        });

        Schema::table('workflow_step_conditions', function (Blueprint $table) {
            $table->unsignedInteger('order')->after('workflow_show_step_id')->default(0);
            $table->renameColumn('condition_type', 'virtual_step');
            $table->dropForeign('workflow_step_conditions_workflow_show_step_id_foreign');
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
        Schema::dropIfExists('workflow_form_submissions_steps');
        Schema::dropIfExists('workflow_form_submission_outputs');

        Schema::table('workflow_step_conditions', function (Blueprint $table) {
            $table->dropColumn('order');
            $table->renameColumn('virtual_step', 'condition_type');
        });
    }
}
