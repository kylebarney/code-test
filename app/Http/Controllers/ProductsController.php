<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ]);

        return Product::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return $product;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ]);

        return $product->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // Delete the product's image to prevent orphan images.
        Storage::delete($product->image);

        return $product->delete();
    }

    /**
     * Attach an image to the specified product.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function uploadImage(Request $request, Product $product) {
        $request->validate([
            'product_image' => 'required|file|image'
        ]);

        $filePath = Storage::putFile('products', $request->file('product_image'));

        // Delete the product's old image before saving the path to the
        // new one in order to free up storage space.
        Storage::delete($product->image);

        $product->image = $filePath;
        return $product->save();
    }

    /**
     * Attach a product to the requesting user.
     * 
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function attachToUser(Product $product) {
        return Auth::user()->products()->attach($product->id);
    }

    /**
     * Detach a product from the requesting user.
     * 
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function detachFromUser(Product $product) {
        return Auth::user()->products()->detach($product->id);
    }

    /**
     * Show the products currently attached to the requesting user.
     * 
     * @return \Illuminate\Http\Response
     */
    public function showUserProducts() {
        return Auth::user()->products;
    }
}
