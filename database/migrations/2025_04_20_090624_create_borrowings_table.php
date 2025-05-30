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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId("item_id")->constrained("items")->cascadeOnDelete();
            $table->foreignId("user_id")->constrained("users")->cascadeOnDelete();
            $table->integer("quantity");
            $table->enum("status", ['pending','approved','rejected','returned'])->default("pending");
            $table->foreignId("approved_by")->nullable()->constrained("admins")->cascadeOnDelete();
            $table->dateTime("approved_at")->nullable();
            $table->date("due_date")->default(\Illuminate\Support\Carbon::now()->addDay(7));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
