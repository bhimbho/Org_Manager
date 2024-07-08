<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganisationCollection;
use App\Http\Resources\OrganisationResource;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserOrganisationController extends Controller
{
    public function get_organisations()
    {
        $organisations = auth()->user()->organisations()->with('owner')->get();

        return response()->json([
            'status' => 'success',
            'message' => '<message>',
            'data' => [
                'organisations' => OrganisationResource::collection($organisations),
            ]
        ]);
    }

    public function get_organisation(Organisation $organisation): JsonResponse
    {
        $organisation = auth()->user()->organisations()->wherePivot('orgId', $organisation->orgId)->firstOrFail();
        return response()->json([
            'status' => 'success',
            'message' => '<message>',
            'data' => [
                new OrganisationResource($organisation),
            ]
        ]);
    }
    public function add(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string',
                'description' => 'string|sometimes'
            ]
        );
        $org = Organisation::create([
            'name' => ucfirst($request->name) . '\'s Organisation',
            'description' => $request->description ?? null,
            'owner_id' => $request->user()->userId
        ]);
        $request->user()->organisations()->attach($org);
        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created',
            'data' => new OrganisationResource($org->load('owner'))
        ], Response::HTTP_CREATED);
    }

    public function add_member(Request $request, Organisation $organisation)
    {
        $request->validate(['userId' => 'required|uuid|exists:users,userId']);
        if ($organisation->users()->wherePivot('userId', $request->get('userId'))->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User already exists',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $organisation->users()->attach($request->get('userId'));
        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ]);
    }
}
