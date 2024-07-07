<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Organisation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessClient;
use Tests\TestCase;
use function Psy\debug;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        );
    }
    /**
     * A basic test example.
     */
    public function test_token_expires_at_configured_time()
    {
        $user = User::factory()->create(
            ['password' => 'password']
        );
        Passport::actingAs($user);
        $token = $user->createToken('test token');
        $expiration = Carbon::parse($token->token->expires_at);
        $now = Carbon::now()->addDay()->addMinute(1);
        $this->assertTrue($expiration->lt($now));
    }

    public function test_correct_user_is_found_in_token()
    {
        $user = User::factory()->create(
            ['password' => 'password']
        );
        Passport::actingAs($user);
        $token = $user->createToken('test token');
        $this->assertEquals($user->userId, $token->token->user_id);
    }

    public function test_user_cant_see_data_in_unauthorised_organisation()
    {
        $userA = User::factory()->create(
            ['password' => 'password']
        );
        $userB = User::factory()->create();
        $org = Organisation::factory()->create([
            'owner_id' => $userA->userId,
            'name' => 'Test Organisation',
        ]);
        $userA->organisations()->attach($org);
        Passport::actingAs($userB);
        $response = $this->getJson("/api/organisations/$org->id");
        $response->assertJson([
            'data' => [
                'organisations' => []
            ]
        ]);
    }

    public function test_fails_when_email_is_missing()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => 'Test',
            'lastName' => 'HNG',
        ]);

        $response->assertSee('error');
        $response->assertJsonValidationErrorFor('email');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_fails_when_firstname_is_missing()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'lastName' => 'HNG',

        ]);

        $response->assertJsonValidationErrorFor('firstName');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_fails_when_lastname_is_missing()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',

        ]);

        $response->assertJsonValidationErrorFor('lastName');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_fails_when_password_is_missing()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',
            'lastName' => 'Kara',
        ]);

        $response->assertJsonValidationErrorFor('password');
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_fail_on_duplicate_emails()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',
            'lastName' => 'Kara',
            'password' => 'password',
        ]);
        $response->assertSuccessful();
        $response2 = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',
            'lastName' => 'Kara',
            'password' => 'password',
        ]);
        $response2->assertJsonValidationErrorFor('email');
    }

    public function test_accurate_default_org_name_is_generated()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',
            'lastName' => 'Tepo',
            'password' => '123456xxAS',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'user' => [
                    'organisations' => [
                        0 => [
                            'name' => 'Kemo\'s Organisation',
                        ],
                    ],
                ],
            ],
        ]);
        $response->assertSee('accessToken');
    }

    public function test_response_contains_expected_user_details()
    {
        $response = $this->postJson('/auth/register', [
            'email' => 'x@mail.com',
            'firstName' => 'Kemo',
            'lastName' => 'Tepo',
            'password' => '123456xxAS',
        ]);

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'user' => [
                    'email' => 'x@mail.com',
                    'firstName' => 'Kemo',
                    'lastName' => 'Tepo',
                    'organisations' => [
                        0 => [
                            'name' => 'Kemo\'s Organisation',
                            'description' => null
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertSuccessful();

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password1'
        ]);
        $response->assertJson(['message' => 'Authentication failed']);
    }
}
