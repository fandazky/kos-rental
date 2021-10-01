<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $listingQuery = Listing::with(['facilities:name,icon_url', 'photos:title,photo_url']);
        $listingQuery->where('is_active', 1);
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
        return response()->json([
            'data' => 'this is availability api'
        ]);
    }
}
