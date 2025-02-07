<?php

namespace App\Models;

use App\Models\Animal;
use App\Models\Category;
use App\Models\FirebaseNotification;
use Illuminate\Database\Eloquent\Model;
use \TCG\Voyager\Models\Role;

class UserAdoptionAnimal extends Model
{

    protected $fillable = [
        'user_id',
        'animal_id',
        'co_owner',
        'meta_data',
        'status',
        'read',
    ];

    public function animal()
    {
        return $this->hasOne('App\Models\Animal', 'id', 'animal_id');
    }
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    public function getMetaDataAttribute($value)
    {
        if ($value) {
            $role = Role::where('name', 'admin')->first();
            $superAdmin = Role::where('name', 'super_admin')->first();
            if (auth()->user()) {
                if (auth()->user()->role_id == $role->id || auth()->user()->role_id == $superAdmin->id) {
                    return $value;
                }
            }
            $value = json_decode($value);
            return $value;
        }
    }
    // This function is use to show user pets
    public static function showUserPet()
    {
        // $userPet = [];
        $ownedPush = [];
        $co_owned = [];
        $data = [];
        $status = config("mpawer-setting.adoption_status");
        $owned = UserAdoptionAnimal::where('user_id', auth()->user()->id)->whereNull('co_owner')->with('animal')->with('animal.seller')->get();
        $coOwner = UserAdoptionAnimal::where('user_id', auth()->user()->id)->where('co_owner', '1')->with('animal')->with('animal.seller')->get();

        try {
            if (sizeof($owned)) {
                foreach ($owned as $key => $value) {
                    $value->animal->race = Category::find($value->animal->race_id);
                    array_push($ownedPush, $value);
                }
                $data['owned'] = $ownedPush;
            } else {
                $data['owned'] = [];
            }
            if (sizeof($coOwner)) {
                foreach ($coOwner as $key => $value) {
                    $value->animal->race = Category::find($value->animal->race_id);
                    array_push($co_owned, $value);
                }
                $data['co_owned'] = $co_owned;
            } else {
                $data['co_owned'] = [];
            }

            $message = "Success";
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

    //This function is use to add new animal as pet
    public static function addUserPet($data)
    {

        $status = config("mpawer-setting.adoption_status");
        try {
            $animal = Animal::where('id', $data->animal_id)->first();
            if (!$animal) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => 'Animal Not Found',
                    'code' => '200',
                ]);
            }
            if ($data->type == 'co_owner') {
                $adoption = UserAdoptionAnimal::create([
                    'animal_id' => $data->animal_id,
                    'user_id' => auth()->user()->id,
                    'co_owner' => 1,
                    'status' => $status['inreview'],
                    'meta_data' => json_encode($data->detail),
                ]);
            } else {
                $adoption = UserAdoptionAnimal::create([
                    'animal_id' => $data->animal_id,
                    'user_id' => auth()->user()->id,
                    'status' => $status['inreview'],
                ]);
            }
            $data = array([
                'adption' => $adoption,
            ]);
            $message = "Success";
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

    //This function is use to delete a adoption request
    public static function deleteAdoption($id)
    {
        try {
            $adoption = UserAdoptionAnimal::find($id);
            $animal_id = $adoption->animal_id;

            $status = config("mpawer-setting.adoption_status");
            $adoptions = UserAdoptionAnimal::where('animal_id', $animal_id)->where('status', $status['inreview'])->get();
            if (!sizeof($adoptions)) {
                $animal = Animal::where('animal', $animal_id)->first();
                $animal->adoption_status = $status['pending'];
                $animal->save();
            }
            $adoption->delete();

            $message = "Success Adoption removed successfully";
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

    //This function is use to approve a adopion request
    public static function approveAdptionRequest($id)
    {
        try {
            $status = config("mpawer-setting.adoption_status");
            $adoption = UserAdoptionAnimal::find($id);

            if ($adoption) {
                $animal = Animal::where('id', $adoption->animal_id)->first();
                if ($adoption->co_owner == '1') {
                    $animal->adoption_status = $status['co_approve'];
                    $adoption->status = $status['co_approve'];

                } else {
                    $animal->adoption_status = $status['approve'];
                    $adoption->status = $status['approve'];
                }
                $animal->save();
                $adoption->save();
            }
            $data = array([
                'animal' => $animal,
            ]);
            $message = "Adoption approved.";

            if ($adoption->user_id != auth()->user()->id) {

                $notificationData = [
                    "title" => "Adoption Request Approved",
                    "body" => auth()->user()->name . " approved your animal adoption request",
                    "screen" => "App",
                    "to_user" => $adoption->user_id,
                    "from_user" => auth()->user()->id,
                    "link" => "/pet-detail/" . $animal->id,
                    "notification_type" => "adoption_approve_reject",
                ];
                FirebaseNotification::sendNotification($notificationData);
            }
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
    //This function is use to reject a adopion request
    public static function rejectAdptionRequest($id)
    {
        try {
            $status = config("mpawer-setting.adoption_status");
            $adoption = UserAdoptionAnimal::find($id);

            if ($adoption) {
                $animal = Animal::where('id', $adoption->animal_id)->first();
                $adoption->status = $status['rejected'];
                $adoption->save();
            }
            $data = array([
                'animal' => $animal,
            ]);
            $message = "Adoption Rejected";
            if ($adoption->user_id != auth()->user()->id) {

                $notificationData = [
                    "title" => "Adoption Request Rejected",
                    "body" => auth()->user()->name . " rejected your animal adoption request",
                    "screen" => "App",
                    "to_user" => $adoption->user_id,
                    "from_user" => auth()->user()->id,
                    "link" => "/pet-detail/" . $animal->id,
                    "notification_type" => "adoption_approve_reject",
                ];
                FirebaseNotification::sendNotification($notificationData);
            }
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
}
