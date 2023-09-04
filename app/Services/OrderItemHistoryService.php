<?php

namespace App\Services;


use App\Models\OrderItemHistory;
use Exception;

class OrderItemHistoryService
{

    public static function observe($orderItem) {

 
        // throw new Exception($orderItem);
        $oldAttributes = $orderItem->getOriginal(); //Old Values
        $newAttributes= $orderItem->getAttributes(); //New Values
       

       
            $oldPrice = data_get($oldAttributes, 'price');
            $newPrice = data_get($newAttributes, 'price');

            $oldQuantity = data_get($oldAttributes, 'quantity');
            $newQuantity = data_get($newAttributes, 'quantity');

            $oldProduct = data_get($oldAttributes, 'product_id');
            $newProduct = data_get($newAttributes, 'product_id');

            if($oldPrice != $newPrice || $oldQuantity != $newQuantity || $oldProduct != $newProduct){
                OrderItemHistory::create([
                    'order_id' => $orderItem->order_id,
                    'item_id' => $orderItem->id,
                    'user_id' => request()->user()->id,
                    'product' => "$oldProduct->$newProduct",
                    'price' => "$oldPrice->$newPrice",
                    'quantity' => "$oldQuantity->$newQuantity",
                ]);
            }
  
        }
           
    

}