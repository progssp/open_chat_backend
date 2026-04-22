<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable("group_message_details")) {
            Schema::create("group_message_details", function (
                Blueprint $table,
            ) {
                $table->id();
                $table->unsignedBigInteger("msg_id");
                $table->unsignedBigInteger("receiver_id");
                $table
                    ->enum("is_seen", ["0", "1"])
                    ->comment("is_conversation_seen")
                    ->default(0);
                $table->softDeletes();
                $table->timestamp("created_at")->useCurrent();
                $table
                    ->timestamp("updated_at")
                    ->nullable()
                    ->default(null)
                    ->useCurrentOnUpdate();
                $table
                    ->foreign("msg_id")
                    ->references("id")
                    ->on("group_messages");
                $table->foreign("receiver_id")->references("id")->on("users");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("group_message_details");
    }
};
