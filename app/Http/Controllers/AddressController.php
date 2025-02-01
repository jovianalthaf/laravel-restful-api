<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    private function getContact(User $user,  $idContact): Contact
    {
        // $contact = Contact::where('id', $idContaact)->where('user_id', $user->id)->first();
        $contact = $user->contacts()->find($idContact);
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "Contact Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $contact;
    }

    private function getAddress(Contact $contact,  $idAddress): Address
    {

        $address = $contact->addresses()->with('contact')->find($idAddress);
        // $address = $contact->addresses()->find($idAddress);
        if (!$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "Address Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }
        return $address;
    }

    public function create($idContact, AddressRequest $request): JsonResponse
    {

        $user = Auth::user();
        // // get current user by id and where idContact current user
        $contact = $this->getContact($user, $idContact);
        // $contact = Contact::where('id', $idContact)->where('user_id', $user->id)->first();


        $data = $request->validated();
        // $address = new Address($data);
        // $address->contact_id = $contact->id;
        // $address->save();
        // create addresses by contact id 
        $address = $contact->addresses()->create($data);

        return (new AddressResource($address))->response()->setStatusCode(201);
    }
    public function get($idContact, $idAddress): AddressResource
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        // $contact = Contact::where('id', $idContact)->where('user_id', $user->id)->first();

        // $address = Address::where('contact_id', $contact->id)->where('id', $idAddress);
        $address = $this->getAddress($contact, $idAddress);
        // $address = $contact->addresses()->find($idAddress);

        return new AddressResource($address);
    }

    public function update($idContact, $idAddress, AddressUpdateRequest $request): AddressResource
    {
        try {
            // if using model binding no need find ID by override,cuz model binding finding id based on parameter API
            // example
            $user = Auth::user();
            $contact = $this->getContact($user, $idContact);
            $address = $this->getAddress($contact, $idAddress);


            $data = $request->validated();

            $address->fill($data);
            $address->save();
            return new AddressResource($address);
        } catch (ModelNotFoundException $e) {
            // Jika address tidak ditemukan, kembalikan 404
            return response()->json([
                'errors' => [
                    'message' => ['Address Not Found']
                ]
            ], 404);
        }

        // if (!$address) {
        //     throw response()->json([
        //         'message' => "Address Not Found"
        //     ], 404);
        // }
    }
    public function delete($idContact, $idAddress): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'errors' => [
                    'message' => ['Unauthorized']
                ]
            ], 401);
        }
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);

        $address->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function list($idContact): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);

        $addresses = Address::where('contact_id', $contact->id)->get();

        return (AddressResource::collection($addresses))->response()->setStatusCode(200);
    }
}
