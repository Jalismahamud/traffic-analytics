<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('url');
            $table->string('method', 10)->default('GET');
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->float('response_time', 8, 3)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('ip_address');
            $table->index('status_code');
            $table->index('method');
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_logs');
    }
};
