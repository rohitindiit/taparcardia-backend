<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class AdminController extends Controller
{
    public function addVendor(Request $request)
    {
         $profile ="";
         // Define validation rules
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'status'  => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 200);
        }

        // If validation passes, process the data
        $validatedData = $validator->validated();

         if ($request->hasFile('profile')) {
            
            $file = $request->file('profile');
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads'); // 'public/uploads'

          if($file->move($destinationPath, $fileName))
          {
             $profile = $fileName; 
          }
         
        }

        // Example: Create a new user
        $user = User::create([
            'firstName' => $validatedData['firstname'],
            'lastName'  => $validatedData['lastname'],
            'email'     => $validatedData['email'],
            'status'    => $validatedData['status'],
            'role' =>"2",
            'profile'=>$profile,
            'password'  =>  Hash::make($validatedData['password']), 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor registered successfully!',
            'user' => $user,
        ], 201);
    }

     public function addVendorProduct(Request $request)
    { 
         $profile ="";
         // Define validation rules
        $validator = Validator::make($request->all(), [
            'CoinName' => 'required|string|max:255',
            'NumCoins'  => 'required|string|max:255',
            'Price'  => 'required|string',
            'Vandor'     => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 200);
        }

        // If validation passes, process the data
        $validatedData = $validator->validated();

         if ($request->hasFile('product_image')) {
            
            $file = $request->file('product_image');
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads'); // 'public/uploads'

          if($file->move($destinationPath, $fileName))
          {
             $profile = $fileName; 
          }
         
        }

        // Example: Create a new user
        $user = Product::create([
            'product_name' => $validatedData['CoinName'],
            'number_of_coins'  => $validatedData['NumCoins'],
            'price'     => $validatedData['Price'],
            'vendor_id'    => $validatedData['Vandor'],
            'product_image'=>$profile,
            
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor registered successfully!',
            'user' => $user,
        ], 201);
    }

    public function vendorList()
    {
       $Vendors =  User::where("role",'=',3)->get();
       return response()->json([
            'status' => 'success',
            'message' => 'Vendor get successfully!',
            'data' => $Vendors,
        ], 201);
    }

    public function addUser(Request $request)
    {
         $profile ="";
         // Define validation rules
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'status'  => 'required',
            'phone'  => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 200);
        }

        // If validation passes, process the data
        $validatedData = $validator->validated();

         if ($request->hasFile('profile')) {
            
            $file = $request->file('profile');
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads'); // 'public/uploads'
            $fileName = preg_replace('/\s+/', '', $fileName);

          if($file->move($destinationPath, $fileName))
          {
             $profile =  $fileName; 
          }
         
        }

        // Example: Create a new user
        $user = User::create([
            'firstName' => $validatedData['firstname'],
            'lastName'  => $validatedData['lastname'],
            'email'     => $validatedData['email'],
            'status'    => $validatedData['status'],
            'phone'  => $validatedData['phone'],
            'role' =>"3",
            'profile'=>$profile,
            'password'  =>  Hash::make($validatedData['password']), 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }

    public function resetPassword(Request $request)
    { 

        
            // Define validation rules
           $validator = Validator::make($request->all(), [
                'user_id'     => 'required',
                'new_password' => 'required|string|min:8|confirmed', 
                
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid password format",
                ], 200);
            }

            // If validation passes, process the data
            $validatedData = $validator->validated();

           $updateStatus = User::where('email', $request->email)->update(['password'=>Hash::make($validatedData['password'])]);

           
                 // Check if the update was successful
        if ($updateStatus > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully!',
            ], 200);
        }

        // If no rows were affected, something went wrong
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to reset the password. Please try again.',
        ], 400);
    }


    public function list_user()
    {
        $data =  $users = User::where("role", "=", 2)->orderBy("created_at", "desc")->get()->map(function ($user) {
            $user->profile = $user->profile ? url("uploads/" . $user->profile) : null;
            return $user;
        });
        return response()->json([
            'status' => 'success',
            'user' => $data,
        ], 201);
    }


    public function viewUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'user_id'     => 'required',
               
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid Request",
                ], 200);
            }

            // If validation passes, process the data
            $validatedData = $validator->validated();
            $user = User::where("id", "=", $request->user_id)->first();

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


    public function deleteUser(Request $request)
    {
         $validator = Validator::make($request->all(), [
                'user_id'     => 'required',
               
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid Request",
                ], 200);
            }

            // If validation passes, process the data
            $validatedData = $validator->validated();

            User::where("id","=",$request->user_id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Record delete!',
            ], 200);
        
    }

   

}
