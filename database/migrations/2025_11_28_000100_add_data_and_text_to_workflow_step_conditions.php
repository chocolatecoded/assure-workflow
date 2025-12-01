<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataAndTextToWorkflowStepConditions extends Migration
{
	public function up()
	{
		Schema::table('workflow_step_conditions', function (Blueprint $table) {
			$table->json('data')->nullable()->after('value');
			$table->string('text')->nullable()->after('data');
		});
	}

	public function down()
	{
		Schema::table('workflow_step_conditions', function (Blueprint $table) {
			$table->dropColumn(['data', 'text']);
		});
	}
}


