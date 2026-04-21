<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('s3_key', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'sort_order']);
        });

        // Copy existing single-image data into the new table as sort_order=0.
        if (Schema::hasColumn('products', 'image_s3_key')) {
            DB::table('products')
                ->whereNotNull('image_s3_key')
                ->orderBy('id')
                ->each(function ($p) {
                    DB::table('product_images')->insert([
                        'product_id' => $p->id,
                        's3_key'     => $p->image_s3_key,
                        'sort_order' => 0,
                        'created_at' => $p->created_at ?? now(),
                        'updated_at' => $p->updated_at ?? now(),
                    ]);
                });

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('image_s3_key');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_s3_key', 500)->nullable()->after('status');
        });

        // Restore the cover image (sort_order=0) back onto products.image_s3_key.
        DB::table('product_images')
            ->where('sort_order', 0)
            ->orderBy('product_id')
            ->each(function ($img) {
                DB::table('products')
                    ->where('id', $img->product_id)
                    ->update(['image_s3_key' => $img->s3_key]);
            });

        Schema::dropIfExists('product_images');
    }
};
