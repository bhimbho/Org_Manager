<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->foreignUuid('userId')->index()->constrained('users', 'userId');
            $table->foreignUuid('orgId')->index()->constrained('organisations', 'orgId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_user_organisation_tables');
    }
};
