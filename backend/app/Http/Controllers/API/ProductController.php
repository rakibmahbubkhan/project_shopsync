<?php

namespace App\Http\Controllers\API;
use App\Models\Product;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;



class ProductController extends Controller
{

public function index(Request $request)
{
    $query = Product::query();

    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    if ($request->sort_by && $request->order) {
        $query->orderBy($request->sort_by, $request->order);
    }

    return response()->json(
        $query->paginate(10)
    );
}

}