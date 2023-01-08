<?php

namespace App\Http\Controllers\Services;

use App\Models\PointOfSale;
use App\Models\Product;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Productservice
{
    // check product if exist
    public function checkProduct($data)
    {
        $product = Product::where('name', $data['name'])->count();
        if($product > 0) return true;

        return false;
    }

    // get code
    public function getCode()
    {
        $dateNow = Carbon::parse(now())->format('ymd');
        $code = 'Halosis'.$dateNow.'-'.mt_rand(0, 1000).'-WEB';
        return $code;
    }

    // get stock
    public function getStock($params)
    {
        $result = [];
        foreach($params as $item)
        {
            $stock = Stock::where('product_id', $item->id)
            ->selectRaw("
                IFNULL(SUM(quantity), 0) AS totalQuantity
            ")->first();

            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'stock' => $stock->totalQuantity
            ];
        }
        
        return $result;
    }

    // take or input stock
    public function allotmentStock($params, $pos = null )
    {
        $now = Carbon::now()->toDateString();
        
        if($pos) {
            $pos = PointOfSale::where('code', $pos->code)->first();
            $code = $pos->code . " - " . $now;
            $type = 'take stock';
        }
        elseif($type = $params[0]['type'] == 'input') {
            $code = $params[0]['code'];
            $type = 'input stock';
        } else {
            $code = $params[0]['code'];
            $type = 'delete pos';
        }
        
        $user = auth()->user();
        $userId = $user->id;

        foreach($params as $row) { 
            $productId = Product::where('name', $row['name'])->first('id');
            $productId = $productId->id;
            $arr = [
                "created_at" => "'{$now}'",
                "updated_at" => "'{$now}'",
                "user_id" => "$userId",
                "product_id" => "$productId",
                "code" => "'$code'",
                "quantity" => ($type == 'input stock' || $type == 'delete pos') ? $row['quantity'] : $row['quantity'] * -1,
                "type" => "'$type'"
            ];

            $query = "INSERT INTO stocks (%s) VALUES (%s);"; 
            $query = sprintf($query, implode(',', array_keys($arr)), implode(',', $arr));
            DB::insert($query);
        }
    }
}