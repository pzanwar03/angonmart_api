<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureMarvelLanguagesTablesExist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('id');
                $table->json('flag');
                $table->string('language_code');
                $table->string('language_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('item_type');
                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('translation_item_id')->nullable();
                $table->string('language_code');
                $table->string('source_language_code')->default(DEFAULT_LANGUAGE);
                $table->timestamps();
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
        Schema::dropIfExists('translations');
        Schema::dropIfExists('languages');
    }
}
