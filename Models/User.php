<?php

namespace App\Models;

use App\Models\Post;
use App\Models\UserAdoptionAnimal;
use App\Models\UserLikeAnimal;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;
use \TCG\Voyager\Models\Role;

class User extends \TCG\Voyager\Models\User implements JWTSubject
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'social_id',
        'social_provider',
        'email',
        'password',
        'email_verification',
        'role_id',
        'address',
        'city',
        'country',
        'postal_code',
        'longitude',
        'latitude',
        'avatar',
        'prefiltering',
        'meta_data',
        'token',
        'gender',
        'contact',
        'forget_otp_code',
        'country_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification',
        'email_verified_at',
        'role_id',
        'token',
        'deleted_at',
        'updated_at',
        'forget_otp_code',
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getAvatarAttribute($value)
    {
        if ($value) {
            $google = "google";
            $facebook = "facebook";
            if (Str::contains($value, $google) || Str::contains($value, $facebook)) {
                return $value;
            }
            if (!auth()->user()) {
                $s3Link = 'https://' . config('filesystems.disks.s3.bucket') . '.s3' . '.amazonaws.com/';
                $value = $s3Link . $value;
                return $value;
            }
            $role = Role::where('name', 'admin')->first();
            $superAdmin = Role::where('name', 'super_admin')->first();
            if (auth()->user()->role_id == $role->id || auth()->user()->role_id == $superAdmin->id) {
                return $value;
            }
            $s3Link = 'https://' . config('filesystems.disks.s3.bucket') . '.s3' . '.amazonaws.com/';
            $value = $s3Link . $value;
            return $value;
        }
    }
    /**
     * This function is use to register a user.
     */
    public static function signup($data)
    {
        try {
            $roleData = Role::where('name', $data->role)->first();
            if (!$roleData) {
                return ([
                    'message' => 'Bad Request',
                    'code' => '',
                ]);
            }
            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
                'role_id' => $roleData->id,
                'address' => $data->address,
                'city' => $data->city,
                'country' => $data->country,
                'postal_code' => $data->postal_code,
                'longitude' => $data->longitude,
                'latitude' => $data->latitude,
                'prefiltering' => json_encode($data->prefiltering),
                'meta_data' => json_encode($data->meta_data),
                'token' => $data->token,
                'gender' => $data->gender,
                'contact' => $data->contact,
                'country_code' => $data->country_code,
            ]);
            $user->roles()->sync($roleData->id);
            if ($roleData->name == 'seller') {
                $user->status = config("mpawer-setting.seller_status.pending");
                $user->update();
            }
            $token = JWTAuth::fromUser($user);
            $user->role = $roleData->name;

            $message = "User Register Successfully";
            $data = array(
                'user' => $user,
                'token' => "Bearer " . $token,
            );
            return ([
                'data' => $data,
                'message' => $message,
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to login a user.
     */
    public static function login($data)
    {
        $credentials = [];
        $credentials['email'] = $data->email;
        $credentials['password'] = $data->password;
        $token = null;
        $user = User::where('email', $data->email)->first();
        if (!$user) {
            return ([
                'message' => 'Email not exists',
                'code' => '',
            ]);
        }

        $role = Role::where('id', $user->role_id)->first();
        if (!$role) {
            return ([
                'message' => 'Bad Request',
                'code' => '',
            ]);
        }
        if (isset($data->role) && $role->name != $data->role) {
            return ([
                'message' => 'You are not authorized to login this panel',
                'code' => '',
            ]);
        }
        $credentials['role_id'] = $role->id;
        $user->makeHidden(['password']);
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return ([
                    'message' => 'Password is incorrect',
                    'code' => '',
                ]);
            }

            $roleData = Role::where('id', $user->role_id)->first();
            $user->role = $roleData->name;
            $message = "Login successfully";
            $data = array(
                'user' => $user,
                'token' => 'Bearer ' . $token,
            );
            return ([
                'data' => $data,
                'message' => $message,
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to delete a user.
     */
    public static function deleteUser()
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return ([
                    'message' => "User Not Found",
                    'code' => '',
                ]);
            }
            $user->forceDelete();
            Auth::guard('api')->logout();
            $message = "User deleted Successfully";
            return ([
                'data' => [],
                'message' => $message,
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to update user profile.
     */
    public static function updateProfile($data)
    {
        try {
            $user = User::find(auth()->user()->id);
            foreach ($data as $key => $value) {
                if ($key == "prefiltering" || $key == "meta_data") {
                    $user->$key = json_encode($value);
                } elseif ($key == 'role' || $key == 'avatar' || $key == 'settings') {
                    continue;
                } else {
                    $user->$key = $value;
                }
            }
            $user->update();
            $data = array(
                'user' => $user,
            );
            $message = "User Updated successfully";
            return ([
                'data' => $data,
                'message' => $message,
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to update user password.
     */
    public static function updatePassword($data)
    {
        $token = null;
        $credentials = [];
        try {
            $user = Auth::User();
            $role = Role::where('id', $user->role_id)->first();
            if (!$role) {
                return ([
                    'message' => 'Bad Request',
                    'code' => '',
                ]);
            }
            $oldToken = $data->bearerToken();
            if (Hash::check($data->old_password, $user->password)) {
                $roleData = Role::where('name', 'seller')->first();
                if ($roleData && $user->role_id == $roleData->id) {
                    $user->update([
                        'password' => Hash::make($data->password),
                    ]);
                } else {
                    $user->update([
                        'password' => Hash::make($data->password),
                    ]);
                }
                $credentials['email'] = $user->email;
                $credentials['password'] = $data->password;
                $credentials['role_id'] = $user->role_id;

                JWTAuth::setToken($oldToken)->invalidate();
                $token = JWTAuth::attempt($credentials);

                $roleData = Role::where('id', $user->role_id)->first();
                $user->role = $roleData->name;
                $data = array(
                    'user' => $user,
                    'token' => 'Bearer ' . $token,
                );
                $message = "Password Updated successfully";
                return ([
                    'data' => $data,
                    'message' => $message,
                    'code' => 200,
                ]);
            } else {
                return ([
                    'message' => "Sorry,You enter wrong old password.",
                    'code' => '',
                ]);
            }
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to generate and send otp to user email.
     */
    public static function requestOtp($data)
    {
        try {
            if (isset($data->role)) {
                $roleData = Role::where('name', $data->role)->first();
                $user = User::where('email', $data->email)->where('role_id', $roleData->id)->first();
            }else {
                $user = User::where('email', $data->email)->first();
            }
            if ($user) {
                $otp = rand(100000, 999999);
                $user->update([
                    'forget_otp_code' => $otp,
                ]);
                $fromEmail = config('mail.from.address');
                $fromName = config('mail.from.name');

                Mail::send('emails.otpCodeRequest', ["detail" => $user], function ($m) use ($fromEmail, $fromName, $user, $otp) {
                    $m->from($fromEmail, $fromName);
                    $m->to($user->email);
                    $m->subject("Forgot Password");
                });

                $message = "OTP sent successfully to " . $user->email . " .";

                return ([
                    'data' => [],
                    'message' => $message,
                    'code' => 200,
                ]);
            } else {
                return ([
                    'message' => 'User not found.',
                    'code' => '',
                ]);
            }
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     * This function is use to verify user otp.
     */
    public static function verifyOtp($data)
    {
        try {
            $user = User::where('email', $data->email)->first();
            if ($user) {
                if ($user->forget_otp_code === $data->otp) {
                    $message = "OTP verified !";
                    return ([
                        'data' => [],
                        'message' => $message,
                        'code' => 200,
                    ]);
                } else {
                    return ([
                        'message' => 'OTP is incorrect.',
                        'code' => '',
                    ]);
                }
            } else {
                return ([
                    'message' => 'User not found.',
                    'code' => '',
                ]);
            }
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    /**
     *This function is use to update user password.
     */
    public static function newPassword($data)
    {
        $token = null;
        $credentials = [];
        try {
            $user = User::where('email', $data->email)->first();
            if ($user) {
                $user->update([
                    'password' => Hash::make($data->password),
                    'otp_code' => null,
                ]);

                $credentials['email'] = $user->email;
                $credentials['password'] = $data->password;
                $credentials['role_id'] = $user->role_id;

                $token = JWTAuth::attempt($credentials);

                $roleData = Role::where('id', $user->role_id)->first();
                $user->role = $roleData->name;
                $data = array(
                    'user' => $user,
                    'token' => 'Bearer ' . $token,
                );
                $message = "Password Updated successfully";
                return ([
                    'data' => $data,
                    'message' => $message,
                    'code' => 200,
                ]);
                $message = "Password updated successfully.";
                return ([
                    'data' => [],
                    'message' => $message,
                    'code' => 200,
                ]);
            } else {
                return ([
                    'message' => 'User not found.',
                    'code' => '',
                ]);
            }
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    // This function is use to upload user profile image
    public static function uploadProfileImage($data)
    {
        $folder = config("mpawer-setting.user_profile_folder");
        try {
            $user = User::find(auth()->user()->id);
            if (isset($data->avatar)) {
                $user->avatar = $data->avatar;
                $user->update();
                $data = array(
                    'user' => $user,
                );
                $message = "Profile updated successfully.";
                return ([
                    'data' => $data,
                    'message' => $message,
                    'code' => 200,
                ]);
            }
        } catch (\Exception $e) {
            return ([
                'message' => $e->getMessage(),
                'code' => '',
            ]);
        }
    }

    public static function profilePage()
    {
        $adoptions = UserAdoptionAnimal::where('user_id', auth()->user()->id)->count();
        $likeAnimal = UserLikeAnimal::where('user_id', auth()->user()->id)->where('like', '1')->count();
        $post = Post::where('user_id', auth()->user()->id)->count();

        $data = array(
            'adption' => $adoptions,
            'like' => $likeAnimal,
            'post' => $post,
        );
        return ([
            'data' => $data,
            'message' => 'success',
            'code' => 200,
        ]);
    }
}
