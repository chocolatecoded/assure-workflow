<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTables extends Migration
{
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            // Align with microservice: allow soft deletes
            if (!Schema::hasColumn('workflows', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workflow_id');
            $table->string('name');
            // Replicate microservice schema
            $table->string('module')->nullable(); // e.g., COMPOSER | APPROVAL
            $table->string('type')->nullable();   // e.g., SIGNIN | VISIT | SIGNOUT
            $table->text('data')->nullable();     // JSON/text config
            $table->string('condition_citeria')->nullable(); // Any | All (kept name to mirror source)
            $table->unsignedInteger('order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
        });

        Schema::create('workflow_step_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workflow_step_id');
            $table->string('condition_type')->nullable();
            $table->string('condition_id')->nullable();
            $table->string('match_type')->nullable();
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            // Part 2: optional show-step foreign key
            $table->unsignedInteger('workflow_show_step_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('workflow_step_id')->references('id')->on('workflow_steps')->onDelete('cascade');
            $table->foreign('workflow_show_step_id')->references('id')->on('workflow_steps')->onDelete('set null');
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workflow_id');
            $table->string('status');
            $table->json('context')->nullable();
            $table->morphs('subject');
            $table->timestamps();
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_step_conditions');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
    }
}

