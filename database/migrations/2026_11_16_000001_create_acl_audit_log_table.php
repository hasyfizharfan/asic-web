<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acl_audit_log', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('id_user')->nullable();
            $table->string('user_email');
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('user_account')->nullOnDelete();
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acl_audit_log');
    }
};
