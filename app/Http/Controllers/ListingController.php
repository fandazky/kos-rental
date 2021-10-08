<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $validator = Validator::make($request->all(), [
            'keyword' => ['nullable','string'],
            'sort_by' => ['nullable','string',Rule::in(['price'])],
            'sort_type' => ['nullable','string',Rule::in(['ASC', 'DESC'])],
            'min_price' => ['nullable','numeric'],
            'max_price' => ['nullable','numeric'],
            'page' => ['nullable', 'numeric'],
            'page_size' => ['nullable','numeric']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $safeRequest = $validator->validated();
        
        $listingQuery = Listing::with(['facilities:name,icon_url', 'photos:title,photo_url']);
        $listingQuery->where('is_active', '=', 1);

        if (isset($safeRequest['keyword'])) {
            $keyword = $safeRequest['keyword'];
            $listingQuery->where(function($query) use ($keyword) {
                $query->where('title', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('address', 'LIKE', '%'.$keyword.'%');
            });
        }
        
        if (isset($safeRequest['sort_by'])) {
            $sortBy = $safeRequest['sort_by'];
            $direction = $request->query('sort_type', 'ASC');
            $listingQuery->orderBy($sortBy, $direction);
        }

        if (isset($safeRequest['min_price'])) {
            $minPrice = $safeRequest['min_price'];
            $listingQuery->where('price', '>=', $minPrice);
        }

        if (isset($safeRequest['max_price'])) {
            $maxPrice = $safeRequest['max_price'];
            $listingQuery->where('price', '<=', $maxPrice);
        }

        $total = $listingQuery->count();
        
        $page = isset($safeRequest['page']) ? $safeRequest['page'] : 1;
        $pageSize = isset($safeRequest['page_size']) ? $safeRequest['page_size'] :10;
        $listingQuery->offset(($page-1)* $pageSize)->limit($pageSize);

        $result = $listingQuery->get();
        $lastPage = ceil($total/$pageSize);

        return response()->json([
            'data' => $result,
            'total' => $total,
            'has_more' => $lastPage > $page,
            'sql' => $listingQuery->toSql()
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
