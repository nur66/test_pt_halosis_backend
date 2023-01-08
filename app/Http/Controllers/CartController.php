<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Services\Productservice;
use App\Models\PointOfSale;

class CartController extends Controller
{
    public function __construct(Productservice $productService) {
        $this->productService = $productService;
    }

    public function store(Request $request) 
    {
        try{
            DB::transaction(function () use ($request, &$pos) {
                $user = $request->user()->getOriginal();

                $data = $request->all();
                if(isset($data['products'])) {
                    $details = $this->getDetailParams($data);
                    
                    $code = $this->productService->getCode();

                    $params = [
                        'user_id' => $user['id'],
                        'code' => $code,
                        'productDetail' => json_encode($details),
                        'dateExpiry' => now()->addHours(2)->toDateTimeString()
                    ];

                    $pos = PointOfSale::create($params);
                    $this->productService->allotmentStock($details, $pos);
                }
            });
            $pos = PointOfSale::findOrFail($pos->id);

            $result = [
                'Id' => $pos->id,
                'CreatedAt' => $pos->created_at,
                'UpdatedAt' => $pos->updated_at,
                'User' => $pos->user,
                'POSCode' => $pos->code,
                'product' => json_decode($pos->productDetail)
            ];

            return response()->json(
                $result,
                Response::HTTP_OK
            );

        } catch(\Exception $e) {
            return response()->json(
                // errmsg($e),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    protected function getDetailParams($data)
    {
        $details = [];
        if(count($data['products']) > 0) $details = $data['products'];
        return $details;
    }

}
