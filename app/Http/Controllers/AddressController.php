<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function create($idContact, AddressRequest $request): JsonResponse
    {

        $user = Auth::user();
        // // get current user by id and where idContact current user
        $contact = $user->contacts()->find($idContact);
        // $contact = Contact::where('id', $idContact)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "Contact Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data = $request->validated();
        // $address = new Address($data);
        // $address->contact_id = $contact->id;
        // $address->save();

        // create addresses by contact id 
        $address = $contact->addresses()->create($data);

        return (new AddressResource($address))->response()->setStatusCode(201);
    }
}
