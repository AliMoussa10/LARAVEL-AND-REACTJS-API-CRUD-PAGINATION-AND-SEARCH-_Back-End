<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Products::select('id','name','price','description','photo')->get();
    }

    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show(Products $product)
    {
        return response()->json([
            'product'=>$product
        ]);
    }
    
    
    public function search($key)
{
   return Products::where('name','LIKE',"%".$key."%")
       ->orwhere('description','LIKE',"%".$key."%")
       ->orwhere('price','=',"$key")
->get();
}
    
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            $imageName = Str::random() . '.' . $request->photo->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('product/image', $request->photo, $imageName);
            Products::create($request->post() + ['photo' => $imageName]);

            return response()->json([
                'message' => 'Product Created Successfully!!'
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something goes wrong while creating a product!!'
            ], 500);
        }
    }

  

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
 public function update(Request $request, Products $product)
    {
        $request->validate([
            'name'=>'required',
            'price'=>'required',
            'description'=>'required',
            'photo'=>'nullable'
        ]);

        try{

            $product->fill($request->post())->update();

            if($request->hasFile('photo')){

                // remove old image
                if($product->photo){
                    $exists = Storage::disk('public')->exists("product/image/{$product->photo}");
                    if($exists){
                        Storage::disk('public')->delete("product/image/{$product->photo}");
                    }
                }

                $imageName = Str::random().'.'.$request->photo->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('product/image', $request->photo,$imageName);
                $product->photo = $imageName;
                $product->save();
            }

            return response()->json([
                'message'=>'Product Updated Successfully!!'
            ]);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while updating a product!!'
            ],500);
        }
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Products $product)
    {
        try {

            if($product->image){
                $exists = Storage::disk('public')->exists("product/image/{$product->photo}");
                if($exists){
                    Storage::disk('public')->delete("product/image/{$product->photo}");
                }
            }

            $product->delete();

            return response()->json([
                'message'=>'Product Deleted Successfully!!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while deleting a product!!'
            ]);
        }
    }
}
