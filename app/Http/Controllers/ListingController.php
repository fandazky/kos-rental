<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ListingController extends Controller
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
        $listingQuery->where('is_active', '=', 1);

        if ($keyword = $request->query('keyword')) {
            $listingQuery->where(function($query) use ($keyword) {
                $query->whereRaw("title LIKE '%". $keyword ."%' OR address LIKE '%". $keyword ."%'");
            });
        }
        
        if ($sortBy = $request->query('sort_by')) {
            $direction = $request->query('sort_type', 'ASC');
            $listingQuery->orderBy($sortBy, $direction);
        }

        $total = $listingQuery->count();
        
        $page = $request->query('page', 1);
        $pageSize = $request->query('page_size', 10);
        $listingQuery->offset(($page-1)* $pageSize)->limit($pageSize);

        $result = $listingQuery->get();
        $lastPage = ceil($total/$pageSize);

        return response()->json([
            'data' => $result,
            'total' => $total,
            'has_more' => $lastPage > $page
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $listingQuery = Listing::with(['facilities:name,icon_url', 'photos:title,photo_url']);
        $listingQuery->where('id', $id);
        $listingQuery->where('is_active', 1);

        $listing = $listingQuery->first();
        
        return response()->json([
            'data' => $listing
        ]);
    }

    /**
     * Show listing availability
     * 
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function availability($id, Request $request) {

        $validator = Validator::make($request->all(), [
            'check_in_date' => ['date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $safeRequest = $validator->validated();

        $result = DB::transaction(function () use ($id, $safeRequest) {
            $listing = Listing::where([
                ['id', '=', $id],
                ['is_active', '=', 1]
            ])->first();
            
            if (!$listing) {
                return [
                    'statusCode' => 404,
                    'data' => [
                        'message' => 'listing not found'
                    ]
                ];
            }

            if ($listing->quantity < 1) {
                return [
                    'statusCode' => 200,
                    'data' => [
                        'available' => false
                    ]
                ];
            }

            $user = User::lockForUpdate()->find($this->user->id);
            if ($user->credit < 5) {
                return [
                    'statusCode' => 404,
                    'data' => [
                        'message' => 'User credit not enough!'
                    ]
                ];
            }

            $user->credit = $user->credit - 5;
            $user->save();
            
            return [
                'statusCode' => 200,
                'data' => [
                    'available' => true
                ]
            ];
        });
        
        return response()->json($result['data'], $result['statusCode']);

    }
}
