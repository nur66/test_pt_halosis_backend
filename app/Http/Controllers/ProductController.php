<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\Productservice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct(Productservice $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::get();

        $product = $this->productService->getStock($products);
        return response()->json([
            "success" => true,
            "message" => "Produk berhasil ditampilkan",
            "data" => $product
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
        $user = auth()->user();
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        // check if product already exist or not
        $check = $this->productService->checkProduct($request->all());
        if ($check === true) {
            $product = Product::where('name', $request->name)->first();

            // Param for inseert into stock
            $code = $this->productService->getCode();
            $details[] = [
                'name' => $product->name,
                'code' => $code,
                'quantity' => $request->quantity,
                'type' => 'input'
            ];

            // Store into Stock
            $this->productService->allotmentStock($details);
        } else {
            $product = $user->product()->create([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            // Param for inseert into stock
            $code = $this->productService->getCode();
            $details[] = [
                'name' => $product->name,
                'code' => $code,
                'quantity' => $request->quantity,
                'type' => 'input'
            ];

            // Store into Stock
            $this->productService->allotmentStock($details);
        }

        $result = [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $request->quantity
        ];

        return response()->json([
            "success" => true,
            "message" => "Produk berhasil ditambahkan",
            "data" => $result
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
        $product = Product::find($id);
        if ($product) {
            return response()->json([
                "success" => true,
                "message" => "Produk berhasil ditampilkan",
                "data" => $product
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Produk tidak ada",
                "data" => null
            ]);
        }
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
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $product = Product::find($id);
        $product->name = $request->name;
        $product->price = $request->price;
        $product->save();

        return response()->json([
            "success" => true,
            "message" => "Produk berhasil diubah",
            "data" => $product
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
        $product = Product::find($id);
        $product->delete();

        return response()->json([
            "success" => true,
            "message" => "Produk berhasil dihapus",
            "data" => $product
        ]);
    }
}
