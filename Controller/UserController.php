<?php

namespace App\Http\Controllers;

use App\Helpers\ApiJsonResponseHelper;
use App\Models\Advertisment;
use App\Models\FirebaseNotification;
use App\Models\Post;
use App\Models\PostLikeComments;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class UserController extends Controller
{
    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to signup function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

/**
 * @OA\Post(
 * path="/api/auth/register",
 * summary="Register New User",
 * tags={"Authentication"},
 *   @OA\Parameter(
 *       name="name",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="email",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string",
 *          format="eamil"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="password",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string",
 *          format="password"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="password_confirmation",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string",
 *          format="password"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="role",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="address",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="postal_code",
 *       in="query",
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="city",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="country",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="longitude",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="latitude",
 *       in="query",
 *       required=true,
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="gender",
 *       in="query",
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Parameter(
 *       name="contact",
 *       required=true,
 *       in="query",
 *       @OA\Schema(
 *          type="string"
 *          )
 *       ),
 * @OA\Response(
 *    response=200,
 *    description="Success",
 *    @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Success")
 *        )
 *     )
 * )
 */

    public function register(Request $request)
    {
        if ($request->role == 'admin') {
            return ApiJsonResponseHelper::errorResponse('You can not register as admin. Sorry!  ');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email:filter|max:255|unique:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6',
            'role' => 'required',
            'address' => 'max:255',
            'city' => 'required',
            'country' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'contact' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->signup($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::apiJsonAuthResponse($data['data']['user'], $data['data']['token'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to login function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login User",
     *     tags={"Authentication"},
     *   @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="eamil"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="password",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:filter|exists:users,email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->login($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::apiJsonAuthResponse($data['data']['user'], $data['data']['token'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function to expire the JWT token. So current user can be logout.
     */

    /**
     * @OA\Post(
     * path="/api/auth/logout",
     * summary="Logout",
     * security={{"bearer_token":{}}},
     * tags={"Authentication"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        if (isset($request->device_token)) {
            $check = FirebaseNotification::where('user_id', auth()->user()->id)->where('device_token', $request->device_token)->first();
            if ($check) {
                $check->delete();
            }
        }
        Auth::guard('api')->logout();
        return ApiJsonResponseHelper::successResponse([], 'Successfully logged out');
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to updateProfile function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/auth/update-profile",
     * security={{"bearer_token":{}}},
     * summary="Upate a User",
     * tags={"Authentication"},
     *   @OA\Parameter(
     *       name="name",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="address",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="postal_code",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="city",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="country",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="longitude",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="latitude",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="gender",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="contact",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'max:255',
            'email' => 'email:filter|max:255|unique:users,email',
            'address' => 'max:255',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->updateProfile($request->all());
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to User model to deleteUser function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Delete(
     * path="/api/auth/delete",
     * summary="Delete User",
     * security={{"bearer_token":{}}},
     * tags={"Authentication"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function deleteUser()
    {
        $user = new User();
        $data = $user->deleteUser();
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function to to show the login user.
     */

    /**
     * @OA\Get(
     * path="/api/auth/show-user",
     * summary="Show Login User",
     * security={{"bearer_token":{}}},
     * tags={"Authentication"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showUser()
    {
        $user = User::find(auth()->user()->id);
        $user->prefiltering = json_decode($user->prefiltering);
        $user->meta_data = json_decode($user->meta_data);

        $data = array(
            'user' => $user,
        );
        return ApiJsonResponseHelper::successResponse($data, 'Success');
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to updatePassword function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/auth/update-password",
     * summary="Update User Password",
     * security={{"bearer_token":{}}},
     * tags={"Authentication"},
     *  @OA\Parameter(
     *       name="old_password",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="password",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="password_confirmation",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:6',
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->updatePassword($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::apiJsonAuthResponse($data['data']['user'], $data['data']['token'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to requestOtp function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/auth/request-otp",
     * summary="Request for OTP",
     * tags={"Authentication"},
     *  @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="email"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:filter|exists:users,email',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->requestOtp($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to verifyOtp function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */
    /**
     * @OA\Post(
     * path="/api/auth/verify-otp",
     * summary="Verify User otp",
     * tags={"Authentication"},
     *  @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="email"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="otp",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="string"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:filter|exists:users,email',
            'otp' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->verifyOtp($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to newPassword function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */
    /**
     * @OA\Post(
     * path="/api/auth/update-new-password",
     * summary="Update User Password after forget password",
     * tags={"Authentication"},
     * @OA\Parameter(
     *       name="email",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="email"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="password",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="password_confirmation",
     *       in="query",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          format="password"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function newPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:filter|exists:users,email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required_with:password|same:password|min:6',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->newPassword($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::apiJsonAuthResponse($data['data']['user'], '', $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to User model to uploadProfileImage function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */
    /**
     * @OA\Post(
     * path="/api/auth/upload-avatar",
     * summary="Upload User Profile image",
     * security={{"bearer_token":{}}},
     * tags={"Authentication"},
     *  @OA\Parameter(
     *       name="avatar",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function uploadProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $user = new User();
        $data = $user->uploadProfileImage($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to User model to profilePage function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */
    /**
     * @OA\Get(
     * path="/api/user/get-count",
     * summary="Get Profile page counts",
     * security={{"bearer_token":{}}},
     * tags={"Profile"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function profilePage()
    {
        $user = new User();
        $data = $user->profilePage();
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }
    /**
     * Then it redirect the user to Advertisment model to showAtAdoptionFlow function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */
    /**
     * @OA\Get(
     * path="/api/auth/show-adoption-advertisment",
     * summary="Show add in adoption flow",
     * security={{"bearer_token":{}}},
     * tags={"Advertisment"},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Success")
     *        )
     *     )
     * )
     */
    public function showAdoptionFlowAdd()
    {
        $advertisment = new Advertisment();
        $data = $advertisment->showAtAdoptionFlow();
        return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
    }

    public function adminUserDelete($user_id)
    {
        $user = User::find($user_id);
        if ($user) {
            $postLikeComments = PostLikeComments::where('user_id', $user_id)->get();
            if (sizeOf($postLikeComments)) {
                foreach ($postLikeComments as $key => $value) {
                    $post = Post::find($value->post_id);
                    if ($value->like == null) {
                        $post->total_comment = $post->total_comment - 1;
                    } else {
                        $post->total_like = $post->total_like - 1;
                    }
                    $post->save();
                    $value->delete();
                }
            }
            $user->delete();
        }
        return redirect(url('/') . '/admin/users');
    }
}
