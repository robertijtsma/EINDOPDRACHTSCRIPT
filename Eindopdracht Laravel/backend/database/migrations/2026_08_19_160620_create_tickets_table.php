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
    Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // De klant
        $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete(); // De toegewezen admin
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->string('title');
        $table->text('description');
        $table->string('status')->default('open'); // statussen: open, in_behandeling, gesloten
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
