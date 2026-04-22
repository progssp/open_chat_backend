<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class LoginAndAuthTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function test_user_login_and_check_auth(): void
    {
        $this->artisan("passport:client --personal --no-interaction");
        // $user = User::factory()->create([
        //     "email" => "test@app.com",
        //     "password" => Hash::make("sample"),
        // ]);

        $loginResponse = $this->postJson("/system/api/user/login", [
            "username" => "jwlssp",
            "password" => "sample",
        ]);

        $loginResponse
            ->assertStatus(200)
            ->assertCookie("token")
            ->assertJsonStructure(["status", "msg", "user", "token"]);

        $token = $loginResponse->json("token");
        // \Log::info("token from test: " . $token);
        $authResponse = $this->withCredentials()
            ->withCookie("token", $token)
            ->postJson("/system/api/user/check-auth");

        $authResponse
            ->assertStatus(200)
            ->assertJson(["status" => true, "msg" => "auth successful"]);

        // $response->assertStatus(200);
    }
}
