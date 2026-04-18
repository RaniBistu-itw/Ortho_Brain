<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('category_id')->nullable()->constrained('products_category');
            $table->foreignId('subcategory_id')->nullable()->constrained('products_subcategory');
            $table->decimal('base_price', 10, 2);
            $table->integer('number_of_revisions')->nullable();
            $table->integer('product_term_months')->nullable();
            $table->integer('from_step')->nullable();
            $table->integer('to_step')->nullable();
            $table->string('url', 500)->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->string('image_s3_key', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
