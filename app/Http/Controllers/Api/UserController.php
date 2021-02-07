<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\User;
use App\Models\UserWallet;
use Exception;
use Validator;

class UserController extends ApiController
{

    /**
     * get user via token 
     *
     * @param Request $request
     * @return jspn
     */
    public function userDetail(Request $request, User $userObj)
    {
        try {
            $user = $userObj->whereToken($request->header('Authorization'))->first();
            if (!$user) {
                return $this->sendResponse(0, 'User not found', null, 200);
            }
            return $this->sendResponse(1, 'user get Successfully', $user, 200);
        } catch (Exception $e) {
            return $this->sendResponse(0, 'Something went wrong', null, 500);
        }
    }

    /**
     * verify user via phone number and email
     *
     * @param Request $request
     * @return json
     */
    public function verifyUser(Request $request, User $userObj)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number_verify' => 'required|numeric|between:0,1',
                'email_verify' => 'required|numeric|between:0,1',
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, $validator->errors()->first(), null, 422);
            }
            $user = $userObj->whereToken($request->header('Authorization'))->first();
            if ($request->email_verify == 1) {
                $user->email_verified_at = date('Y-m-d H:i:s');
            }
            if ($request->phone_number_verify == 1) {
                $user->phone_number_verified_at = date('Y-m-d H:i:s');
            }
            $user->save();
            $data['id'] = $user->id;
            $data['first_name'] = $user->first_name;
            $data['last_name'] = $user->last_name;
            return $this->sendResponse(1, 'User verify Successfully', $data, 200);
        } catch (Exception $e) {
            return $this->sendResponse(0, 'Something went wrong', null, 500);
        }
    }

    /**
     * transfer money via email and phone number
     *
     * @param Request $request
     * @return json
     */
    public function transferMoney(Request $request, UserWallet $userWalletObj, User $userObj)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required',
            'amount' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendResponse(0, $validator->errors()->first(), null, 422);
        }
        $user = $request->user;
        $receiverUser = $userObj->where('email',$user)->orWhere('phone_number',$user)->first();
        $senderUser = $userObj->whereToken($request->header('Authorization'))->first();
        if(!$senderUser->email_verified_at || !$senderUser->phone_number_verified_at){
            return $this->sendResponse(0, 'You are not verify', null, 200);
        }
        $amount = (int)$request->amount;
        $senderAmount = $senderUser->amount;
        $receiveAmount = $receiverUser->amount;
        if($senderAmount >= $amount){
            // increase amount to receiver user
            $receiverUser->amount = $receiveAmount + $amount;
            $receiverUser->save();

            // decrease amount from sender user
            $senderUser->amount = $senderAmount - $amount;
            $senderUser->save();
            
            // save history in user waller table
            $userWalletObj->sender_id = $senderUser->id;
            $userWalletObj->receiver_id = $receiverUser->id;
            $userWalletObj->amount = $amount;
            $userWalletObj->save();
            return $this->sendResponse(1, 'Your money successfully transferd', $userWalletObj, 200);
        }else{
            return $this->sendResponse(0, 'You have not sufficient amount', null, 200);
        }
    }
}
