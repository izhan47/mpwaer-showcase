<?php

namespace App\Models;

use App\Models\CustomNotifications;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use \TCG\Voyager\Models\Role;

class FirebaseNotification extends Model
{
    protected $fillable = [
        'user_id',
        'device_token',
    ];

    public static function store($data)
    {
        $check = FirebaseNotification::where('device_token', $data->device_token)->get();
        if (sizeof($check)) {
            $message = "Token already exists";
            return ([
                'data' => [],
                'message' => $message,
                'code' => 200,
            ]);
        }
        $store = FirebaseNotification::create([
            'user_id' => auth()->user()->id,
            'device_token' => $data->device_token,
        ]);
        $message = "Success";
        return ([
            'data' => $store,
            'message' => $message,
            'code' => 200,
        ]);
    }

    public static function sendNotification($notiData)
    {
        $notificaiton = CustomNotifications::newNotification($notiData);
        $notificaiton = CustomNotifications::where('id', $notificaiton->id)->with("fromUser")->get();

        $firebaseToken = FirebaseNotification::where('user_id', $notiData['to_user'])->get();
        $SERVER_API_KEY = config('mpawer-setting.firebase.key');
        if (sizeof($firebaseToken)) {
            foreach ($firebaseToken as $key => $value) {
                if ($value->device_token) {
                    $data = [
                        "to" => $value->device_token,
                        "notification" => [
                            "title" => $notiData['title'],
                            "body" => $notiData['body'],
                            "screen" => $notiData['screen'],
                        ],
                        "data" => [
                            "title" => $notiData['title'],
                            "body" => $notiData['body'],
                            "screen" => $notiData['screen'],
                            "link" => $notiData['link'],
                            "notification_type" => $notiData['notification_type'],
                            "notifiaciton_details" => $notificaiton,
                            //you can get this data as extras in your activity and this data is optional
                        ],
                    ];
                    $dataString = json_encode($data);
                    $headers = [
                        'Authorization: key=' . $SERVER_API_KEY,
                        'Content-Type: application/json',
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                    $response = curl_exec($ch);
                }
            }
        }
    }

    public static function pushNotification($notiData)
    {
        $role = Role::where('name', 'admin')->first();
        $user = User::where('role_id', $role->id)->first();

        $data = [];
        $data['title'] = $notiData->title;
        $data['body'] = $notiData->body;
        $data['screen'] = "App";
        $data['from_user'] = $user->id;
        $data['notification_type'] = "admin_push_notitcation";
        $data['link'] = "/";
        if ($notiData->to_user == "all") {
            $roleData = Role::where('name', 'user')->first();
            $firebaseToken = User::where('role_id',$roleData->id)->get();
            foreach ($firebaseToken as $key => $value) {
                $data['to_user'] = $value->id;
                FirebaseNotification::sendNotification($data);
            }
        } else {
            foreach ($notiData->to_user as $key => $value) {
                $data['to_user'] = $value;
                FirebaseNotification::sendNotification($data);
            }
        }
        return ([
            'message' => "Success",
            'code' => 200,
        ]);
    }
}
