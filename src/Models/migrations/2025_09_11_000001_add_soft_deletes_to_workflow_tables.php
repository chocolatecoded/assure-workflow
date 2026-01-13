<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesToWorkflowTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('workflows', 'deleted_at')) {
            Schema::table('workflows', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('workflow_steps', 'deleted_at')) {
            Schema::table('workflow_steps', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('workflow_step_conditions') && !Schema::hasColumn('workflow_step_conditions', 'deleted_at')) {
            Schema::table('workflow_step_conditions', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('workflows', 'deleted_at')) {
            Schema::table('workflows', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('workflow_steps', 'deleted_at')) {
            Schema::table('workflow_steps', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('workflow_step_conditions') && Schema::hasColumn('workflow_step_conditions', 'deleted_at')) {
            Schema::table('workflow_step_conditions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
}

