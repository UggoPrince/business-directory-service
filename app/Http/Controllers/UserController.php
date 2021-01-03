<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use \Firebase\JWT\JWT;
use App\Models\User;

/**
 * @OA\Post(
 * path="/api/login",
 * summary="Sign in",
 * description="Login by email, password",
 * operationId="authLogin",
 * tags={"auth"},
 * @OA\RequestBody(
 *    required=true,
 *    description="Pass user credentials",
 *    @OA\JsonContent(
 *       required={"email","password"},
 *       @OA\Property(property="email", type="string", format="email", example="admin@mail.com"),
 *       @OA\Property(property="password", type="string", format="password", example="Password"),
 *    ),
 * ),
 * @OA\Response(
 *    response=200,
 *    description="Success response",
 *    @OA\JsonContent(
 *       @OA\Property(property="status", type="integer", example=200),
 *       @OA\Property(property="message", type="string", example="User successfully retrieved"),
 *       @OA\Property(property="data", type="object", example={"id": 1,
 *       "email": "admin@mail.com",
 *       "created_at": "2021-01-02T15:28:39.000000Z",
 *       "updated_at": "2021-01-02T15:28:39.000000Z",
 *       "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJleHAiOm51bGwsImlkIjoxLCJlbWFpbCI6ImFkbWluQG1haWwuY29tIn0.y_tl1_zBpO-M3p1Uev-6Uji9AmgQ-zkF1dHi9QZHIgc"}),
 *        )
 *     )
 * )
 * @OA\Response(
 *    response=422,
 *    description="Wrong credentials response",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
 *        )
 *     )
 * )
 */

class UserController extends Controller
{
    public $successStatus = 200;
    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */
    public function login(Request $request) {
        if (User::where('email', $request->email)->exists()) {
            $user = User::where('email', $request->email)->get();
            $u = $user[0];
            $isPassCorrect = Hash::check($request->password, $u->password);
            if (!$isPassCorrect) {
                return response()->json([
                    'status' => 400,
                    "message" => "Invalid Email/Password"
                ], 404);
            }
            $payload = array(
                'exp' => config('app.jwt_time'),
                "id" => $u->id,
                'email' => $u->email,
            );            
            $token = JWT::encode($payload, config('app.jwt_secret'), 'HS256');
            $user[0]->token = $token;
            return response()->json([
                'status' => 200,
                'message' => 'User successfully retrieved',
                'data' => $user[0]
            ], 200);
        } else {
            return response()->json([
              'status' => 404,
              "message" => "User not found"
            ], 404);
        }
    }
}
