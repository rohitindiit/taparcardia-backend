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


class BuyerController extends Controller
{
   
  public function accountDetails($user_id)
    {
       
            $user = User::with("addresses")->where("id", "=", $user_id)->first();

           if ($user) {
                $user->profile = $user->profile ? url("uploads/" . $user->profile) : null;
                return response()->json([
                'status' => true,
                'detail'=> $user,
                'message' => 'Record fetched!',
            ], 200);
             
            } else {
                return response()->json([
                'status'  => 'error',
                'message' => 'User not found.',
                ], 404);
            }

        }

     public function updateAccount(Request $request)
        {
           
        $validator = Validator::make($request->all(), [
          'user_id'=>'required'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => "Invalid request",
            ], 200);
        }

            // Find the user by ID
            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 200);
            }

            // Update the user's data
            $user->update([
                'firstName' => $request->firstName,
                'lastName'  => $request->lastName,
                'phone'  => $request->phone,
            ]);

            return response()->json([
                'status' => 'true',
                'message' => 'User updated successfully!',
                'user' => $user,
            ], 200);
        }

        public function update_profile(Request $request)
        {
              if ($request->hasFile('profile')) {
            
                    $file = $request->file('profile');
                    $fileName = uniqid() . '_' . $file->getClientOriginalName();
                    $destinationPath = public_path('uploads'); // 'public/uploads'

                  if($file->move($destinationPath, $fileName))
                  {
                     $profile = $fileName; 
                      $user = User::find($request->user_id);
                        $user->update([
                            'profile'=>$profile,
                        ]);

                        return response()->json([
                            'status' => 'true',
                            'message' => 'Profile changed!',
                          
                        ], 200);
                  }
         
              }
        }

        public function getAllVendor()
        {
            
            $users = User::where("role", "=", 3)->orderBy("created_at", "desc")->get()->map(function ($user) {
            $user->profile = $user->profile ? url("uploads/" . $user->profile) : null;
                    return $user;
                });
            return response()->json([
                    'status' => true,
                    'message' => 'Vendor get successfully!',
                    'data' => $users,
                ], 201);
        }

       public function getAllProducts($vendor_id, $user_id= null)
        {
            if(!$user_id)
            {
                    $products = Product::where("vendor_id", "=", $vendor_id)
                    ->orderBy("created_at", "desc")
                    ->get();
                   
                return response()->json([
                    'status' => true,
                    'message' => 'Products retrieved successfully!',
                    'data' => $products,
                ], 201);
            }

            // Get all product IDs in the user's cart
            $cartProductIds = \DB::table('user_cart')
                ->where('user_id', $user_id)
                ->pluck('product_id')
                ->toArray();

            $products = Product::where("vendor_id", "=", $vendor_id)
                ->orderBy("created_at", "desc")
                ->get()
                ->map(function ($product) use ($cartProductIds) { 
                    $product->product_image = $product->product_image 
                        ? url("uploads/" . $product->product_image) 
                        : null;
                        
                    // Check if the current product exists in the user's cart
                    $product->in_cart = in_array($product->id, $cartProductIds);
                    
                    return $product;
                });

            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully!',
                'data' => $products,
            ], 201);
        }



        public function addToCart(Request $request)
        { 
             $validator = Validator::make($request->all(), [
              'user_id'=>'required'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => "Invalid request",
                ], 200);
            }

           $userCart =  new UserCart();
           $userCart->product_id=$request->product_id;
           $userCart->user_id=$request->user_id;
           $userCart->qty=$request->quantity;
           $userCart->save();

           return response()->json([
                    'status' => true,
                    'message' => 'Added to cart',
                ], 201);


        }

         public function removeToCart(Request $request)
        { 
             $validator = Validator::make($request->all(), [
              'user_id'=>'required',
              'product_id'=>'required'
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => "Invalid request",
                ], 200);
            }

            UserCart::where(['user_id'=>$request->user_id,'product_id'=>$request->product_id])->delete();

           return response()->json([
                    'status' => true,
                    'message' => 'Removed to cart',
                ], 201);


        }

        public function cartCount(Request $request)
        { 
             $validator = Validator::make($request->all(), [
              'user_id'=>'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => "Invalid request",
                ], 200);
            }

            $count = UserCart::where(['user_id'=>$request->user_id])->count();

           return response()->json([
                    'status' => true,
                    'count'=>$count,
                   
                ], 201);


        }

       public function UserCart($user_id)
        {
            // Get cart items with product details using a join
            $cartItems = DB::table('user_cart')
                ->join('products', 'user_cart.product_id', '=', 'products.id')
                ->join('users', 'products.vendor_id', '=', 'users.id') 
                ->where('user_cart.user_id', $user_id)
                ->select(
                    'user_cart.id as cart_id',
                    'user_cart.user_id',
                    'user_cart.product_id',
                    'user_cart.qty',
                    'products.product_name',
                    'products.price',
                    DB::raw("CONCAT('" . url('/uploads/') . "/', products.product_image) as product_image"), // Add full URL
                    'products.vendor_id',
                    'users.firstName as vendor_name', // Selecting vendor name
                    DB::raw('user_cart.qty * products.price as total_price') // Calculate total price
                )
                ->get();

            return response()->json([
                'status' => true,
                'Cart' => $cartItems,
            ], 201);
        }

  


        public function updateCart(Request $request)
        { 
             $validator = Validator::make($request->all(), [
              'cart_id'=>'required',
              'method'=>'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => "Invalid request",
                ], 200);
            }

            try{
                        if( $request->method=="add")
                        {
                             UserCart::where('id', $request->cart_id)
                                ->increment('qty', 1);
                        }

                        if($request->method=="remove"){
                             UserCart::where('id', $request->cart_id)
                                ->decrement('qty', 1);
                        }

                          if($request->method=="delete"){
                             UserCart::where('id', $request->cart_id)
                                ->delete();
                        }
                       

                        return response()->json([
                            'status' => true,        
                        ], 201);
                }catch(error){
                      return response()->json([
                        'status' => false,
                       
                    ], 201);
                }
 

        }

        public function superTotal($user_id)
        {
              $cartItems = DB::table('user_cart')
                    ->join('products', 'user_cart.product_id', '=', 'products.id')
                    ->join('users', 'products.vendor_id', '=', 'users.id')
                    ->where('user_cart.user_id', $user_id)
                    ->select(
                        'user_cart.id as cart_id',
                        'user_cart.user_id',
                        'user_cart.product_id',
                        'user_cart.qty',
                        'products.product_name',
                        'products.price',
                        DB::raw("CONCAT('" . url('/uploads/') . "/', products.product_image) as product_image"),
                        'products.vendor_id',
                        'users.firstName as vendor_name',
                        'user_cart.tex'
                    )
                    ->get();

                // Calculate subtotal, tax amount, and total
                $subtotal = 0;
                $totalTax = 0;
                $grandTotal = 0;
               


                foreach ($cartItems as $item) {
                    $item->subtotal = $item->price * $item->qty;  // Calculate subtotal
                    $item->tax_amount = ($item->subtotal * $item->tex) / 100; // Tax calculation
                    $item->total = $item->subtotal + $item->tax_amount; // Total price after tax

                    // Accumulate overall totals
                    $subtotal += $item->subtotal;
                    $totalTax += $item->tax_amount;
                    $grandTotal += $item->total;
                }

                return response()->json([
                     'status' => true,
                     'cal'=>[
                         'subtotal' => number_format($subtotal, 2),
                     'total_tax' => number_format($totalTax, 2),
                     'grand_total' => number_format($grandTotal, 2),
                     ],
                     'cart'=>$cartItems,
                    
                    
                ], 200);
        }


        public function addUserAddress(Request $request)
        {
                    $validator = Validator::make($request->all(), [
                      'user_id'=>'required',
                     
            
                    ]);

                    // Check if validation fails
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'errors' => "Invalid request",
                        ], 200);
                    }

                    if($request->is_default=="1")
                    {
                       UserAddress::query()->update(['is_default' => 0]);

                    }

                   $userAddress =  new UserAddress();
                   $userAddress->user_id=$request->user_id;
                   $userAddress->fname=$request->fname;
                   $userAddress->lname=$request->lname;
                   $userAddress->address_type=$request->address_type;
                   $userAddress->address_line1=$request->address_line1;
                   $userAddress->address_line2=$request->address_line2;
                   $userAddress->city=$request->city;
                   $userAddress->state=$request->state;
                   $userAddress->postal_code=$request->postal_code;
                   $userAddress->country=$request->country;
                   $userAddress->phone=$request->phone;
                   $userAddress->is_default=$request->is_default;
                   $userAddress->save();



                    return response()->json([
                            'status' => true,
                            'message' => "Address saved",
                        ], 200);

        }

       public function updateUserAddress(Request $request)
            {
                // Validate input
                $validator = Validator::make($request->all(), [
                    'id' => 'required|exists:user_address',
                    'address_type'  => 'required|in:billing,shipping',
                    'address_line1' => 'required|string|max:255',
                    'address_line2' => 'nullable|string|max:255',
                    'city'          => 'required|string|max:100',
                    'state'         => 'required|string|max:100',
                    'postal_code'   => 'required|string|max:20',
                    'country'       => 'required|string|max:100',
                    'phone'         => 'required|string|max:15',
                    
                ]);

                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $validator->errors(),
                    ], 422);
                }

                 if($request->is_default=="1")
                    {
                       UserAddress::query()->update(['is_default' => 0]);

                    }
                // Find the address record
                $userAddress = UserAddress::where('id', $request->id)->first(); 
                
                if (!$userAddress) {
                    return response()->json([
                        'status' => false,
                        'message' => "Address not found",
                    ], 404);
                }

                // Update address details
                $userAddress->update([
                    'fname'=>$request->fname,
                    'lname'=>$request->lname,
                    'address_type'  => $request->address_type,
                    'address_line1' => $request->address_line1,
                    'address_line2' => $request->address_line2,
                    'city'          => $request->city,
                    'state'         => $request->state,
                    'postal_code'   => $request->postal_code,
                    'country'       => $request->country,
                    'phone'         => $request->phone,
                    'is_default'    => $request->is_default ?? 0,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => "Address updated successfully",
                ], 200);
            }


        public function getUserAddress($user_id)
        {
           

             $all_address = UserAddress::where(["user_id"=>$user_id])->get();

              return response()->json([
                    'status' => true,
                    'data' => $all_address,
                ], 200);

        }

         public function getUserSingleAddress($address_id)
        {
           

             $address = UserAddress::where(["id"=>$address_id])->first();

              return response()->json([
                    'status' => true,
                    'data' => $address,
                ], 200);

        }

         public function deleteUserAddress(Request $request)
            {
                $validator = Validator::make($request->all(), [
                      'address_id'=>'required',
                     
            
                    ]);

                    // Check if validation fails
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => false,
                            'errors' => "Invalid request",
                        ], 200);
                    }

                try{
                     $all_address = UserAddress::where(["id"=>$request->address_id])->delete();

                  return response()->json([
                        'status' => true,
                        'message' => "Address delete successfully",
                    ], 200);
                }catch(error){

                      return response()->json([
                            'status' => false,
                            'message' => "Can't remove message",
                        ], 200);
                }

                

            }

       public function placeOrder(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'billing_address_id' => 'required',
                'payment_method_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => "Fill out all required field",
                ], 200);
            }

            try {
                DB::beginTransaction();  // Start transaction for atomic operations

                $user_id = $request->user_id;
                $address_id = $request->billing_address_id;
                $payment_method_id = $request->payment_method_id;

                // Get cart items
                $cartItems = UserCart::where('user_id', $user_id)->get();
                if ($cartItems->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cart is empty',
                    ], 200);
                }

                // Get billing address
                $billingAddress = UserAddress::where('id', $address_id)->first();
                if (!$billingAddress) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid billing address',
                    ], 200);
                }

                // Calculate total order price
                $totalPrice = $cartItems->sum(function ($cartItem) {
                    return $cartItem->qty * $cartItem->product->price;  // Assuming product relationship exists
                });

                // Create order
                $order = Order::create([
                    'user_id' => $user_id,
                    'vendor_id' => 16,  // Set vendor ID dynamically if needed
                    'total_price' => $totalPrice,
                    'order_status' => 'pending',  // Default status
                    'payment_status' => 'pending',  // Default payment status
                    'shipping_address' => json_encode($billingAddress),  
                    'billing_address' => json_encode($billingAddress),
                    'payment_method' => $payment_method_id
                ]);

                // Insert order items
                foreach ($cartItems as $item) { 

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_title'=>$item->product->product_name,
                        'total_price' => $item->qty * $item->product->price,
                        'qty' => $item->qty,
                    ]);
                }

                // Clear user cart after placing order
                UserCart::where('user_id', $user_id)->delete();

                DB::commit();  // Commit the transaction

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $order->id
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();  // Rollback transaction if any error occurs

                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while placing the order',
                    'error' => $e->getMessage(),
                ], 500);
            }
}

public function getOrderDetails(Request $request)
{
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:orders,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 400);
    }

    try {
        $orderId = $request->order_id;

        // Retrieve order details
        $order = Order::with(['orderItems'])
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Format response
        $orderDetails = [
            'order_id' => '#'.str_pad($order->id, 8, '0', STR_PAD_LEFT), // e.g., #12345678
            'order_date' => $order->created_at->format('M d, Y'),        // e.g., Nov 16, 2024
            'total_price' => '$'.$order->total_price,                   // e.g., $40
            'order_status' => $order->order_status,                     // e.g., Pending, Delivered
            'products' => $order->orderItems->map(function ($item) {
                return [
                    'product_name' => $item->product_title,    // From `orderitem` table
                    'quantity' => $item->qty,                 // From `orderitem` table
                    'vendor' => 'Vendor Name',                // Add vendor logic if required
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



public function getUserOrders(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 400);
    }

    try {
        $userId = $request->user_id;

        // Retrieve all orders for the user with related order items, vendor, and products
        $orders = Order::with(['orderItems.product', 'vendor']) // Fetch related product details
            ->where('user_id', $userId)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No orders found for this user.',
            ], 404);
        }

        // Format response
        $userOrders = $orders->map(function ($order) {
            return [
                'order_id' => '#' . str_pad($order->id, 8, '0', STR_PAD_LEFT), // e.g., #00001234
                'order_date' => $order->created_at->format('M d, Y'), // e.g., Jan 28, 2025
                'total_price' => '$' . $order->total_price, // e.g., $40.00
                'order_status' => $order->order_status, // e.g., Pending, Delivered
                'vendor' => $order->vendor->firstName ?? 'Unknown Vendor', // Fetch vendor name from order
                'products' => $order->orderItems->map(function ($item) {
                    return [
                        'product_name' => $item->product_title ?? 'Unknown Product', // Fetch product name
                        'quantity' => $item->qty, // Quantity from `order_items` table
                        'product_image' => $item->product->product_image ? asset('uploads/' . $item->product->product_image) : asset('uploads/default.png'), // Fetch product image
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $userOrders,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching the user orders.',
        ], 500);
    }
}




}
