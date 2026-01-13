<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurableWorkflowsEnabledToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('company', 'configurable_workflows_enabled')) {
            Schema::table('company', function (Blueprint $table) {
                $table->integer('configurable_workflows_enabled')->default(0)->after('jde_enabled');
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
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn('configurable_workflows_enabled');
        });
    }
}

