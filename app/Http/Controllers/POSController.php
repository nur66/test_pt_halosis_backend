<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\Productservice;
use App\Models\PointOfSale;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function __construct(Productservice $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $result = [];
        $user = auth()->user();
        $pos = PointOfSale::with('user')->where('user_id', $user->id)->get();
        foreach($pos as $item){
            $result[] = [
                'id' => $item->id,
                'code' => $item->code,
                'user' => $item->user,
                'product' => json_decode($item->productDetail)
            ];
        }
        
        return response()->json([
            "success" => true,
            "message" => "Order berhasil ditampilkan",
            "data" => $result
        ]);
    }

    public function show($id)
    {
        $result = $detail = [];
        $pos = PointOfSale::find($id);
        if ($pos) {
            $products = json_decode($pos->productDetail);
            foreach($products as $product) {
                $detail[] = $product;
            }

            $result = [
                'id' => $pos->id,
                'code' => $pos->code,
                'user' => $pos->user,
                'product' => $detail
            ];
            
            return response()->json([
                "success" => true,
                "message" => "Order berhasil ditampilkan",
                "data" => $result
            ]);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Order tidak ada",
                "data" => $result
            ]);
        }
    }

    public function delete($id)
    {
        $pos = PointOfSale::find($id);
        $products = json_decode($pos->productDetail);
        // dd($products);
        foreach($products as $product) {
            $code = $this->productService->getCode();
            $details[] = [
                'name' => $product->name,
                'code' => $code . '-' . 'delete',
                'quantity' => $product->quantity,
                'type' => 'delete'
            ];
            $this->productService->allotmentStock($details);
        }
        $pos->delete();

        return response()->json([
            "success" => true,
            "message" => "Order berhasil dihapus",
            "data" => $pos
        ]);
    }
}
