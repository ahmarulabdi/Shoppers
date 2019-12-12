<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    private $request;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // create new token
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => 'lumen-jwt', // Issuer of the token
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 3600 * 3600
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public function authenticate(User $user)
    {
        $this->validate($this->request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            return response()->json([
                'error' => "Email doesn't exist",
            ], 400);
        }

        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'success' => true,
                'access_token' => $this->jwt($user)
            ], 200);
        }

        return response()->json([
            'error' => 'Email or password is wrong'
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'password' => 'required',
            'email' => 'required|email|unique:users'
        ]);

        if($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->all(),
            ];


        }

        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = app('hash')->make($request->password);
        $user->save();

        unset($user->password);

        return Response::json([
            'success' => true,
            'user' => $user
        ], 200);


    }
}
