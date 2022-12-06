<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $dataSets = [
        'domain1.api-requirements.test' => '{ "products": [ { "sku": "000001", "name": "Domain 1 Full coverage insurance", "category": "insurance", "price": 89000 }, { "sku": "000002", "name": "Compact Car X3", "category": "vehicle", "price": 99000 }, { "sku": "000003", "name": "SUV Vehicle, high end", "category": "vehicle", "price": 150000 }, { "sku": "000004", "name": "Basic coverage", "category": "insurance", "price": 20000 }, { "sku": "000005", "name": "Convertible X2, Electric", "category": "vehicle", "price": 250000 } ] }',
        'domain2.api-requirements.test' => '{ "products": [ { "sku": "000001", "name": "Domain 2 Full coverage insurance", "category": "insurance", "price": 89000 }, { "sku": "000002", "name": "Compact Car X3", "category": "vehicle", "price": 99000 }, { "sku": "000003", "name": "SUV Vehicle, high end", "category": "vehicle", "price": 150000 }, { "sku": "000004", "name": "Basic coverage", "category": "insurance", "price": 20000 }, { "sku": "000005", "name": "Convertible X2, Electric", "category": "vehicle", "price": 250000 } ] }',
    ];

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $currency = "EUR";
        $dataSet = json_decode($this->dataSets[request()->getHost()]);
        $products = collect($dataSet->products);

        // Execute when there is a category query param.
        $products = $products->when($request->has('category'), function ($products) use ($request) {
            // Filter data by category.
            return $products->filter(function ($product) use ($request) {
                return $product->category === $request->get('category');
            });
        });

        // Execute when there is a price query param.
        $products = $products->when($request->has('price'), function ($products) use ($request) {
            // Filter data by price.
            return $products->filter(function ($product) use ($request) {
                return $product->price == $request->get('price');
            });
        });

        // Map to new data format.
        $products->map(function ($product) use ($currency) {
            $discountPercentage = 0;

            // Apply 15% discount to 000003 only.
            if ($product->sku == 000003) $discountPercentage += 15;

            // Apply 30% discount to insurance category.
            if ($product->category === 'insurance') $discountPercentage += 30;

            // Final price (After discount).
            $finalPrice = $product->price - ($product->price * ($discountPercentage / 100));

            $product->price = [
                'original' => $product->price,
                'final' => ceil($finalPrice),
                'discount_percentage' => $discountPercentage ? "{$discountPercentage}%" : null,
                'currency' => $currency,
            ];

            return $product;
        });
        
        return $products;
    }
}
