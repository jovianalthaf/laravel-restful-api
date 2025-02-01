<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotNull;

class AddressTest extends TestCase
{

    public function testCreateTest()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $IdContact = Contact::query()->latest()->first();

        $this->post(
            '/api/contacts/' . $IdContact->id . '/addresses',
            [
                'street' => 'test2',
                'city' => 'test2',
                'province' => 'test2',
                'country' => 'test2',
                'postal_code' => '10640'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201)->assertJson([
            'data' => [
                'street' => 'test2',
                'city' => 'test2',
                'province' => 'test2',
                'country' => 'test2',
                'postal_code' => '10640'
            ]
        ]);
    }
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/' . $contact->id . '/addresses',
            [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '10640'
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201)->assertJson([
            'data' => [
                'street' => 'test',
                'city' => 'test',
                'province' => 'test',
                'country' => 'test',
                'postal_code' => '10640'
            ]
        ]);
    }
    public function testCreateFailed() {}

    public function testCreateContactNotFound() {}

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();



        $this->get('/api/contacts/' . $address->contact_id . '/addresses/' . $address->id, [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'street' => "test",
                    'city' => "test",
                    'province' => "test",
                    'country' => "test",
                    'postal_code' => "11111",
                ]
            ]);
    }
    public function testGetAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();



        $this->get('/api/contacts/' . $address->contact_id . '/addresses/' . ($address->id + 1), [
            'Authorization' => 'test'
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Address Not Found'
                    ]
                ]
            ]);
    }
    public function testGetContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();



        $this->get('/api/contacts/' . ($address->contact_id + 1) . '/addresses/' . $address->id + 1, [
            'Authorization' => 'test'
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Contact Not Found'
                    ]
                ]
            ]);
    }
    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();
        $this->put(
            '/api/contacts/' . $address->contact_id . '/addresses/' . $address->id,
            [

                'street' => "update",
                'city' => "update",
                'province' => "update",
                'country' => "update",
                'postal_code' => "22222",

            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'street' => "update",
                'city' => "update",
                'province' => "update",
                'country' => "update",
                'postal_code' => "22222",
            ]
        ]);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();
        $this->put(
            '/api/contacts/' . $address->contact_id . '/addresses/' . $address->id,
            [

                'street' => "update",
                'city' => "update",
                'province' => "",
                'country' => "",
                'postal_code' => "22222",

            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'country' => [
                    'The country field is required.'
                ],

            ]
        ]);
    }
    public function testUpdateNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();
        // $nonExistentAddressId = 9999;
        $address = Address::query()->limit(1)->first();
        $nonExistentAddressId = $address->id + 1; // ID yang tidak ada
        $this->put(
            '/api/contacts/' . $address->contact_id . '/addresses/' . $nonExistentAddressId,
            [

                'street' => "update",
                'city' => "update",
                'province' => "update",
                'country' => "update",
                'postal_code' => "22222",

            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['Address Not Found']
            ]
        ]);
    }
    public function testDeleteSuccess()
    {

        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();

        $address = Address::query()->limit(1)->first();
        $this->delete(
            '/api/contacts/' . $address->contact_id . '/addresses/' . $address->id,
            [],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => true
        ]);
    }
    public function testDeleteNotFound() {}

    public function testListSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            '/api/contacts/' . $contact->id . '/addresses',
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                [
                    'street' => "test",
                    'city' => "test",
                    'province' => "test",
                    'country' => "test",
                    'postal_code' => "11111",
                ]
            ]
        ]);
    }
    public function testListNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        // Ambil alamat pertama dengan relasi contact
        // $address = Address::with('contact')->first();
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            '/api/contacts/' . ($contact->id + 1) . '/addresses',
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                'message' => ['Contact Not Found']
            ]
        ]);
    }
}
