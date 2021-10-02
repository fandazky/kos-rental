<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidFormValueException;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','between:2,100'],
            'email' => ['required','string','email','max:100','unique:users'],
            'password' => ['required','string','confirmed','min:6'],
            'roles' => ['required','array'],
            'roles.*' => ['required','string'],
            'is_premium_user' => ['nullable','boolean']
        ]);

        if($validator->fails()){
            return response()->json([ 
                'errors' => $validator->errors()
            ], 400);
        }
        
        $safeRequest = $validator->validated();

        DB::beginTransaction();
        $result = null;
        try {
            $credit = 20;
            if (isset($safeRequest['is_premium_user']) && $safeRequest['is_premium_user']) {
                $credit = 40;
            }
            $user = User::create(array_merge(
                $safeRequest,
                ['password' => bcrypt($request->password), 'credit' => $credit]
            ));

            if ($roleIdList = $safeRequest['roles']) {
                $roles = Role::whereIn('id', $roleIdList)->get();
                if (count($roles) !== count($roleIdList)) {
                    throw new InvalidFormValueException('Some roles id is invalid', 'roles');
                }
                $user->roles()->attach($roleIdList);
            }
            $result = $user;
            DB::commit();
        } catch (InvalidFormValueException $e) {
            DB::rollback();
            return response()->json([
                'errors' => [
                    $e->getFormField() => [
                        $e->getFieldError()
                    ]
                ]
            ], 422);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Unknown Exception'
            ], 422);
        }

        return response()->json([
            'data' => $result
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
