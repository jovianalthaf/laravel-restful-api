<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    private function getContact($idContact)
    {
        $user = Auth::user();
        $contact = $user->contacts->find($idContact);
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
    public function create(ContactCreateRequest $request): ContactResource
    {
        // validated request input from user
        $validated = $request->validated();

        // get current user
        $user = Auth::user();
        // fill column user_id in table contacts using $user->id
        // $validated['user_id'] = $user->id;

        $contact = $user->contacts()->create($validated);
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                "errors" => [
                    "message" => [
                        "Contact Not Found"
                    ]
                ]
            ]));
        }
        return new ContactResource($contact);

        // return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        $contacts = Contact::query()->where('user_id', $user->id);
        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('first_name', 'like', '%' .  $name . '%');
                    $builder->orWhere('last_name', 'like', '%' . $name . '%');
                });
            }

            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }
            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });
        $contacts = $contacts->paginate(perPage: $size, page: $page);

        return new ContactCollection($contacts);
    }

    public function get($id): ContactResource
    {

        // find contact by user id
        $contact = $this->getContact($id);
        // $contact = $user->contacts->find($id);
        // $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        // if (!$contact) {
        //     throw new HttpResponseException(response()->json([
        //         'errors' => [
        //             "message" => [
        //                 "Contact Not Found"
        //             ]
        //         ]
        //     ])->setStatusCode(404));
        // }

        return new ContactResource($contact);
    }

    public function update(ContactUpdateRequest $request, $id): ContactResource
    {

        $user = Auth::user();
        $contact = $this->getContact($id);
        // $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
        // if (!$contact) {
        //     throw new HttpResponseException(response()->json([
        //         'errors' => [
        //             "message" => [
        //                 "User Not Found"
        //             ]
        //         ]
        //     ])->setStatusCode(404));
        // }
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
