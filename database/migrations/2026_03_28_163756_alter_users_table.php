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
        Schema::table('users',function (BluePrint $table){
            $table->string('address')->after('email')->nullable();
            $table->string('city')->after('address')->nullable();
            $table->string('state')->after('city')->nullable();
            $table->string('zip')->after('state')->nullable();
            $table->string('mobile')->after('zip')->nullable();
        });
    }

    /**t
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users',function (BluePrint $table){
            $table->dropColumn('address');
            $table->dropColumn('state');
            $table->dropColumn('zip');
            $table->dropColumn('city');
            $table->dropColumn('mobile');
        });
    }
};
