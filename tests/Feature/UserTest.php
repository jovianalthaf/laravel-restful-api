<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use GuzzleHttp\Psr7\Header;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'jovian',
            'password' => 'rahasia',
            'name' => "Jovian Althaf Sanjaya"
        ])->assertStatus(201)->assertJson([
            "data" => [
                'username' => 'jovian',
                'name' => "Jovian Althaf Sanjaya"
            ]
        ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ""
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    'username' => [
                        "The username field is required."
                    ],
                    'password' => [
                        "The password field is required."
                    ],
                    'name' => [
                        "The name field is required."
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExist()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'jovian',
            'password' => 'rahasia',
            'name' => "Jovian Althaf Sanjaya"
        ])->assertStatus(400)->assertJson([
            "errors" => [
                'username' => [
                    "username already registered"
                ],
            ]
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => "test",
            'password' => "test",
        ])->assertStatus(200)->assertJson([
            "data" => [
                'username' => "test",
                'name' => "test"
            ]
        ]);
        $user = User::where('username', 'test')->first();
        assertNotNull($user->token);
    }
    public function testLoginFailedUsernameNotFound()
    {
        // $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => "test",
            'password' => "test",
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "username or password wrong"
                ]
            ]
        ]);
    }
    public function testLoginSuccessUsernamePasswordWrong()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => "test",
            'password' => "awdadad",
        ])->assertStatus(401)->assertJson([
            "errors" => [
                "message" => [
                    "username or password wrong"
                ]
            ]
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => "test",
                    'name' => 'test'
                ]
            ]);
    }
    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current')->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }
    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    "message" => [
                        "Unauthorized"
                    ]
                ]
            ]);
    }
    public function testUpdateNameSuccess()
    {

        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch(
            '/api/users/current',
            [
                'name' => 'Jovian'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'Jovian'
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        assertNotEquals($oldUser->name, $newUser->name);
    }
    public function testUpdatePasswordSuccess()
    {

        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->patch(
            '/api/users/current',
            [
                'password' => 'baru'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
        ]);
        $newUser = User::where('username', 'test')->first();
        assertNotEquals($oldUser->password, $newUser->password);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch(
            '/api/users/current',
            [
                'name' => 'JovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovian
                JovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovian
                JovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovian
                JovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovian
                JovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovianJovian
                '
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'name' => [
                    'The name field must not be greater than 100 characters.'
                ]
            ]
        ]);
    }

    public function testLogoutSuccess()
    {
        // $this->testLoginSuccess();
        $this->seed([UserSeeder::class]);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            'data' => true
        ]);

        $user = User::where('username', 'test')->first();
        assertNull($user->token);
    }
    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'salah'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                "message" => [
                    "Unauthorized"
                ]
            ]
        ]);
    }

    
}
