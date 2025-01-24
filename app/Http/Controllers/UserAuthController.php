<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function userSignup(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'role'  => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // If validation passes, process the data
        $validatedData = $validator->validated();

        // Example: Create a new user
        $user = User::create([
            'firstName' => $validatedData['firstname'],
            'lastName'  => $validatedData['lastname'],
            'email'     => $validatedData['email'],
            'role'      => $validatedData['role'],
            'password'  =>  Hash::make($validatedData['password']), 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

     
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 201);
        }

        $fourDigitNumber = rand(1000, 9999);

        User::where("email",'=',$request->email)->update(['otp'=>$fourDigitNumber]);

         return response()->json([
            'status' => 'success',
            'message' => 'Otp send successfully!',
            'otp' => $fourDigitNumber,
        ], 201);


    }

    public function verifyOtp(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'otp'  => 'required',
            'email'=> 'required|email|exists:users,email',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "invalid credentials",
            ], 200);
        }

        // If validation passes, process the data
        $validatedData = $validator->validated();

        $user = User::where('email', $request->email)->first();

        // Example: Create a new user
         if ($user && $user->otp === $request->otp) {
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully!',
            ], 200);
        }

        // OTP did not match
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid OTP. Please try again.',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
            // Define validation rules
           $validator = Validator::make($request->all(), [
                'email'     => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed', 
                'password_confirmation' => 'required|string|min:8', 
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


   public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            // Generate and return a personal access token
            $token = $user->createToken('taparcadia')->plainTextToken;

            return response()->json([
                'status' =>true,
                'message' => 'Login successful.',
                'data' => array('user'=>$user,'token'=>$token),
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials.',
        ], 401);
    }

}
