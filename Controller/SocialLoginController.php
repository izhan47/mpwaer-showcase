<?php

namespace App\Http\Controllers;

use App\Helpers\ApiJsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use \TCG\Voyager\Models\Role;

class SocialLoginController extends Controller
{
    public function loginSignUpWithSocial(Request $request)
    {
        if (!isset($request->email)) {
            return response([
                'message' => 'Email Not Found',
            ],400);
        }
        try {
            $roleData = Role::where('name', $request->role)->first();
            if (!$roleData) {
                return ([
                    'message' => 'Bad Request',
                    'code' => '',
                ]);
            }
            $token = null;
            if ($request->social_provider == 'facebook') {
                if (isset($request->email)) {
                    $user = User::where('email', $request->email)->first();
                } else {
                    $user = User::where('social_id', $request->social_id)->first();
                }
            } elseif ($request->social_provider == 'google') {
                $user = User::where('email', $request->email)->where('role_id', $roleData->id)->first();
            } elseif ($request->social_provider == 'apple') {
                $user = User::where('email', $request->email)->where('role_id', $roleData->id)->first();
            } else {
                return ([
                    'message' => 'Sorry Social provider not found',
                    'code' => '',
                ]);
            }
            $password = config("mpawer-setting.login_with_social_password");

            if (isset($user) && $user->role_id != $roleData->id) {
                return ([
                    'message' => 'Sorry This email is already use by seller account.',
                    'code' => '',
                ]);
            }

            if (isset($user)) {
                $oldPassword = $user->password;
                $user->password = Hash::make($password);
                $user->update();

                $token = JWTAuth::fromUser($user);
                $user->token = $request->token;
                $user->password = $oldPassword;
                $user->update();
                $message = "User Login Successfully";
            } else {
                if (!isset($request->name)) {
                    $request->name = 'Anonymous';
                }

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'social_id' => $request->social_id,
                    'social_provider' => $request->social_provider,
                    'password' => Hash::make($password),
                    'role_id' => $roleData->id,
                    'token' => $request->token,
                    'avatar' => $request->avatar,
                ]);
                $user->roles()->sync($roleData->id);
                $token = JWTAuth::fromUser($user);
                $message = "User Register Successfully";
            }

            $user->role = $roleData->name;
            $bearerToken = "Bearer " . $token;

            return ApiJsonResponseHelper::apiJsonAuthResponse($user, $bearerToken, $message);
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }
}
