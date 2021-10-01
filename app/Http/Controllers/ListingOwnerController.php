<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ListingOwnerController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = auth()->user();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $listingQuery = Listing::with(['facilities:name,icon_url', 'photos:title,photo_url']);
        $listingQuery->where('owner_id', '=', $this->user->id);
        if ($sortBy = $request->query('sort_by')) {
            $direction = $request->query('sort_type', 'ASC');
            $listingQuery->orderBy($sortBy, $direction);
        }

        $total = $listingQuery->count();
        
        $page = $request->query('page', 1);
        $pageSize = $request->query('page_size', 10);
        $result = $listingQuery->offset(($page-1)* $pageSize)->limit($pageSize)->get();
        $lastPage = ceil($total/$pageSize);

        return response()->json([
            'data' => $result,
            'total' => $total,
            'has_more' => $lastPage > $page
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required','string','between:4,128'],
            'description' => ['required','string','max:128'],
            'address' => ['required','string'],
            'quantity' => ['required','numeric'],
            'gender_allowed' => ['required', Rule::in(['male', 'female', 'all'])],
            'price' => ['required','numeric'],
            'facilities' => ['required', 'array'],
            'photos' => ['required', 'array']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $safeRequest = $validator->validated();
        

        $result = DB::transaction(function () use ($safeRequest) {
            $listing = new Listing();
            $listing->title = $safeRequest['title'];
            $listing->description = $safeRequest['description'];
            $listing->address = $safeRequest['address'];
            $listing->quantity = $safeRequest['quantity'];
            $listing->gender_allowed = $safeRequest['gender_allowed'];
            $listing->price = $safeRequest['price'];
            $listing->owner_id = $this->user->id;

            $facilityIds = $safeRequest['facilities'];
            $photoIds = $safeRequest['photos'];

            $listing->save();
            if ($facilityIds) {
                $listing->facilities()->attach($facilityIds);
            }
            if ($photoIds) {
                $listing->photos()->attach($photoIds);
            }

            return $listing;
            
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['nullable','string','between:4,128'],
            'description' => ['nullable','string','max:128'],
            'address' => ['nullable','string'],
            'quantity' => ['nullable','numeric'],
            'gender_allowed' => ['nullable', Rule::in(['male', 'female', 'all'])],
            'price' => ['nullable','numeric'],
            'facilities' => ['nullable', 'array'],
            'photos' => ['nullable', 'array']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $safeRequest = $validator->validated();

        $result = DB::transaction(function () use ($id, $safeRequest) {
            $listing = Listing::lockForUpdate()->find($id);

            if (!$listing) {
                return [
                    'statusCode' => 404,
                    'data' => [
                        'message' => "Listing not available"
                    ]
                ];
            };

            if (isset($safeRequest['title'])) {
                $listing->title = $safeRequest['title'];
            }

            if (isset($safeRequest['description'])) {
                $listing->description = $safeRequest['description'];
            }
            
            if (isset($safeRequest['address'])) {
                $listing->address = $safeRequest['address'];
            }

            if (isset($safeRequest['quantity'])) {
                $listing->quantity = $safeRequest['quantity'];
            }

            if (isset($safeRequest['gender_allowed'])) {
                $listing->gender_allowed = $safeRequest['gender_allowed'];
            }
            
            if (isset($safeRequest['price'])) {
                $listing->price = $safeRequest['price'];
            }
            
            if (isset($safeRequest['facilities'])) {
                $facilityIds = $safeRequest['facilities'];
                if ($facilityIds) {
                    $listing->facilities()->detach();
                    $listing->facilities()->attach($facilityIds);
                }
            }

            if (isset($safeRequest['photos'])) {
                $photoIds = $safeRequest['photos'];
                if ($photoIds) {
                    $listing->photos()->detach();
                    $listing->photos()->attach($photoIds);
                }
            }

            $listing->save();

            return [
                'statusCode' => 204,
                'data' => $listing
            ];
            
        });

        return response()->json($result['data'], $result['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = DB::transaction(function () use ($id) {
            $listing = Listing::lockForUpdate()->find($id);

            if (!$listing) {
                return [
                    'statusCode' => 404,
                    'data' => [
                        'message' => "Listing not available"
                    ]
                ];
            };

            if ($listing->owner_id !== $this->user->id) {
                return [
                    'statusCode' => 403,
                    'data' => [
                        'message' => "Unauthorized"
                    ]
                ];
            }

            $listing->is_active = 0;

            return [
                'statusCode' => 204,
                'data' => $listing
            ];
            
        });

        return response()->json($result['data'], $result['statusCode']);
    }
}
