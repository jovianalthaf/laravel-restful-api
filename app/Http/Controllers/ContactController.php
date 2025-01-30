<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): ContactResource
    {
        // validated request input from user
        $validated = $request->validated();

        // get current user
        $user = Auth::user();
        // validated column user_id to $user->id
        $validated['user_id'] = $user->id;

        $contact = Contact::create($validated);

        return new ContactResource($contact);

        // return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function get($id): ContactResource
    {
        $user = Auth::user();
        // find contact by user id
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "User Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return new ContactResource($contact);
    }

    public function update(ContactUpdateRequest $request, $id): ContactResource
    {

        $user = Auth::user();
        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "User Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }
        $validated = $request->validated();
        $contact->fill($validated);
        $contact->save();
        return new ContactResource($contact);
    }

    public function delete($id): ContactResource
    {
        $user = Auth::user();

        $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "Contact Not Found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        $contact->delete();
        return new ContactResource($contact);
    }
}
