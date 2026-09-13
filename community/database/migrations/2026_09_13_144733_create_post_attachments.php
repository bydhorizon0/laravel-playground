<?php

use App\Models\Post;
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
        Schema::create('post_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Post::class)->constrained()->cascadeOnDelete();

            $table->string('disk');
            $table->string('path');

            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['post_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_attachments');
    }
};
