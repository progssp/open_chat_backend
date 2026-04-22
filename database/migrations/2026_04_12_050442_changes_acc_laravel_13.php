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
        Schema::table("oauth_clients", function (Blueprint $table) {
            if (!Schema::hasColumn("oauth_clients", "owner_type")) {
                $table->string("owner_type")->nullable()->after("user_id");
                $table
                    ->unsignedBigInteger("owner_id")
                    ->nullable()
                    ->after("owner_type");
            }

            if (!Schema::hasColumn("oauth_clients", "provider")) {
                $table->string("provider")->nullable()->after("secret");
            }

            if (!Schema::hasColumn("oauth_clients", "redirect_uris")) {
                $table->text("redirect_uris")->nullable()->after("provider");
                $table->text("grant_types")->nullable()->after("redirect_uris");
            }

            $table->dropColumn([
                "user_id",
                "redirect",
                "personal_access_client",
                "password_client",
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("oauth_clients", function (Blueprint $table) {
            if (Schema::hasColumn("oauth_clients", "owner_type")) {
                $table->dropColumn(["owner_type"]);
            }
            if (Schema::hasColumn("oauth_clients", "owner_id")) {
                $table->dropColumn(["owner_id"]);
            }
            if (Schema::hasColumn("oauth_clients", "provider")) {
                $table->dropColumn(["provider"]);
            }
            if (Schema::hasColumn("oauth_clients", "redirect_uris")) {
                $table->dropColumn(["redirect_uris"]);
            }
            if (Schema::hasColumn("oauth_clients", "grant_types")) {
                $table->dropColumn(["grant_types"]);
            }

            $table
                ->unsignedBigInteger("user_id")
                ->nullable()
                ->index()
                ->after("id");
            $table->text("redirect");
            $table->boolean("personal_access_client");
            $table->boolean("password_client");
        });
    }
};
