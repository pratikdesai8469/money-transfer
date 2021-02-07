<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Hash;
use Carbon\Carbon;
use App\User;
use Validator;
use Exception;

class AuthenticationController extends ApiController
{
    /**
     * Assign unique token staring
     *
     * @var string
     */
    private $apiToken;

    public function __construct()
    {
        $this->apiToken = uniqid(base64_encode(str_random(100)));
    }

    /**
     * Register API
     *
     * @param Request $request
     * @return json
     */
    public function register(Request $request,User $userObj)
    {
        try {
            // check validation
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'password' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'phone_number' => 'required|numeric|unique:users|digits:10',
                'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, $validator->errors()->first(), null, 422);
            }
            $userObj->first_name = $request->first_name;
            $userObj->last_name = $request->last_name;
            $userObj->email = $request->email;
            $userObj->phone_number = $request->phone_number;
            $userObj->password = bcrypt($request->password);
            // upload image
            if ($request->hasFile('image')) {
                $path = 'public/upload/user';
                $image = $request->file('image');
                $name = Carbon::now()->format('YmdHisu') . '.' . $image->getClientOriginalExtension();
                $destinationPath = $path;
                $image->move($destinationPath, $name);
                $userObj->image = $path . '/' . $name;
            }
            $userObj->token = $this->apiToken;
            $userObj->save();
            // save user session
            $data['id'] = $userObj->id;
            $data['first_name'] = $userObj->first_name;
            $data['last_name'] = $userObj->last_name;
            $data['token'] = $userObj->token;
            return $this->sendResponse(1, 'User Register Successfully', $data, 200);
        } catch (Exception $e) {
            return $this->sendResponse(0, 'Something went wrong', null, 500);
        }
    }

    /**
     * Login API
     *
     * @param Request $request
     * @return json
     */
    public function login(Request $request, User $userObj)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required_without:email',
                'password' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, $validator->errors()->first(), null, 422);
            }
            $columnName = $request->email ? 'email' : 'phone_number';
            $value = $request->email ? $request->email : $request->phone_number;
            $user = $userObj->where($columnName, $value)->first();
            if (!$user) {
                return $this->sendResponse(0, 'Login Fail, please check ' . $columnName, null, 401);
            }
            // check password with hash
            if (!Hash::check($request->password, $user->password)) {
                return $this->sendResponse(0, 'Login Fail, please check password', null, 401);
            }
            // save user session
            $user->token = $this->apiToken;
            $user->save();
            $data['id'] = $user->id;
            $data['first_name'] = $user->first_name;
            $data['last_name'] = $user->last_name;
            $data['token'] = $user->token;
            return $this->sendResponse(1, 'Login successfully', $data, 200);
        } catch (Exception $e) {
            return $this->sendResponse(0, 'Something went wrong', null, 500);
        }
    }
}
