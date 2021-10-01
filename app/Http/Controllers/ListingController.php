<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

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
        return response()->json([
            'success' => true,
            'data' => 'test'
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
        return response()->json([
            'success' => true,
            'data' => 'this is update api'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => true,
            'data' => 'this is delete api'
        ]);
    }
}
