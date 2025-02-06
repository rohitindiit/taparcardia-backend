<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Product;
use App\Models\UserCart;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\OrderItem;
use App\Models\Order;
use DB;

class VendorController extends Controller
{
      

       public function products(Request $request)
        {

             $validator = Validator::make($request->all(), [
                  'vendor_id'=>'required',
                ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => "Invalid request",
                    ], 200);
                }


                $products = Product::where("vendor_id", $request->vendor_id)
                    ->orderBy("created_at", "desc")
                    ->get()
                    ->map(function ($product) {
                        $product->product_image = url('uploads/' . $product->product_image);
                        return $product;
                    });

                   
                return response()->json([
                    'status' => true,
                    'message' => 'Products retrieved successfully!',
                    'data' => $products,
                ], 201);
           
        }

 public function getVendorOrders(Request $request)
{
    $validator = Validator::make($request->all(), [
        'vendor_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 200);
    }

    try {
        $vendorId = $request->vendor_id;

        // Retrieve vendor orders with related order items and products
        $orders = Order::with(['orderItems.product'])
            ->where('vendor_id', $vendorId)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No orders found for this vendor.',
            ], 200);
        }

        // Format response as per required structure
        $vendorOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'products' => $order->orderItems->map(function ($item) {
                    return $item->product->product_name ?? 'Unknown Product';
                })->join(', '), // Join multiple product names as a single string
                'orderno' => '#' . str_pad($order->id, 4, '0', STR_PAD_LEFT), // e.g., #1225
                'date' => $order->created_at->format('M d, Y'), // e.g., Jun 29, 2020
                'commissionfee' => '$' . number_format($order->commission_fee ?? 0, 2), // Format to dollars
                'amount' => '$' . number_format($order->total_price ?? 0, 2), // Format total amount
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $vendorOrders,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching the vendor orders.',
        ], 500);
    }
}


public function getOrderDetails(Request $request)
{
     $validator = Validator::make($request->all(), [
                  'order_id'=>'required',
                ]);


                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => "Invalid request",
                    ], 200);
                }

    try {

         $order_id = $request->order_id;
        // Retrieve order details with related vendor, user, and products
        $order = Order::with(['orderItems.product', 'user', 'vendor']) // Ensure vendor relationship exists
            ->where('id', $order_id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Format response
        $orderDetails = [
            'order_id' => '#' . str_pad($order->id, 8, '0', STR_PAD_LEFT), // e.g., #00001234
            'vendor_name' => $order->vendor->firstName ?? 'Unknown Vendor', // Fetch vendor name
            'order_date' => $order->created_at->format('d.m.Y'), // e.g., 25.12.2024
            'delivered_date' => $order->delivered_at ? $order->delivered_at->format('d.m.Y') : 'Pending',
            'payment_method' => $order->payment_method, // Credit Card, PayPal, etc.
            'order_status' => $order->order_status, // Delivered, Pending, etc.
            'total_price' => '$' . number_format($order->total_price, 2),
            'user' => [
                'user_id' => $order->user->id ?? null,
                'name' => $order->user->firstName ?? 'Unknown User',
                'email' => $order->user->email ?? 'No Email',
            ],
            'products' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product->product_name ?? 'Unknown Product',
                    'quantity' => $item->qty,
                    'product_image' => $item->product->product_image
                        ? asset('uploads/' . $item->product->product_image)
                        : asset('uploads/default.png'),
                ];
            }),
        ];

        return response()->json([
            'status' => true,
            'data' => $orderDetails,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching the order details.',
        ], 500);
    }
}


public function getProductDetails(Request $request)
{
    $validator = Validator::make($request->all(), [
        'product_id' => 'required|exists:products,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => "Invalid request",
        ], 400);
    }

    // Fetch product details
    $product = DB::table('products')
        ->where('id', $request->product_id)
        ->select('id', 'vendor_id', 'product_name', 'number_of_coins', 'price', 'product_image')
        ->first();

    if (!$product) {
        return response()->json([
            'status' => false,
            'message' => "Product not found",
        ], 404);
    }

    // Format the product image URL
    $product->product_image = $product->product_image 
        ? url('uploads/' . $product->product_image) 
        : url('uploads/default.png');

    return response()->json([
        'status' => true,
        'data' => $product,
    ], 200);
}




}
