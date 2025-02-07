<?php

namespace App\Models;

use App\Models\CustomNotifications;
use App\Models\UserAdoptionAnimal;
use App\Models\UserLikeAnimal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \TCG\Voyager\Models\Role;

class Animal extends Model
{

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'vaccine',
        'preffernces',
        'age',
        'date_of_birth',
        'address',
        'city',
        'country',
        'postal_code',
        'longitude',
        'latitude',
        'media',
        'meta_data',
        'region',
        'race_id',
        'gender',
        'featured',
        'description',
        'disclaimer',
        'adoption_status',
        'adoption',
        'co_adoption',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function seller()
    {
        return $this->hasOne('App\Models\User', 'id', 'seller_id');
    }
    public function race()
    {
        return $this->hasOne('App\Models\Category', 'id', 'race_id');
    }
    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }
    public function scopeFilter($q, array $filters)
    {
        $status = config("mpawer-setting.adoption_status.approve");

        $q->when($filters['keyword'] ?? null, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        })->when($filters['gender'] ?? null, function ($q, $gender) {
            $q->where('gender', $gender);
        })->when($filters['region'] ?? null, function ($q, $region) {
            $q->where('region', $region);
        })->when($filters['categoryId'] ?? null, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        })->when($filters['race_id'] ?? null, function ($q, $race_id) {
            $q->whereIn('race_id', $race_id);
        });
        $q->where('adoption_status', '!=', $status);

        return $q;
    }
    public function getMediaAttribute($value)
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
            $s3Link = 'https://' . config('filesystems.disks.s3.bucket') . '.s3' . '.amazonaws.com/';
            foreach ($value as $media_key => $media_value) {
                $value[$media_key] = $s3Link . $media_value;
            }
            return $value;
        }
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
        }
        return $value;
    }

    public static function store($data)
    {
        try {
            $mediaLink = [];
            $folder = config("mpawer-setting.animal_media_folder");
            $animal = new Animal();
            if (auth()->user()) {
                $animal->seller_id = auth()->user()->id;
            } else {
                $role = Role::where('name', 'admin')->first();
                $user = User::where('role_id', $role->id)->first();
                $animal->seller_id = $user->id;
            }
            foreach ($data as $key => $value) {
                if ($key == "media") {
                    $animal->$key = json_encode($value);
                } elseif ($key == "meta_data") {
                    $animal->$key = json_encode($value);
                } else {
                    $animal->$key = $value;
                }
            }
            $animal->adoption_status = config("mpawer-setting.adoption_status.pending");
            $animal->save();

            $countanimalLike = UserLikeAnimal::where('animal_id', $animal->id)->where('like', '1')->count();
            $countAdoptionRequest = UserAdoptionAnimal::where('animal_id', $animal->id)->whereNull('co_owner')->count();
            $countCoAdoptionRequest = UserAdoptionAnimal::where('animal_id', $animal->id)->where('co_owner', '1')->count();
            $animal['likes'] = $countanimalLike;
            $animal['adoption_request'] = $countAdoptionRequest;
            $animal['co_adoption_request'] = $countCoAdoptionRequest;

            $data = array(
                'animal' => $animal,
            );
            $message = "Animal addded successfully";
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
     * This function is use to show all animals.
     */
    public static function showAllAnimal($data)
    {
        try {
            $animals = Animal::query()->filter($data->only('gender', 'region', 'race_id', 'keyword', 'categoryId'))->orderBy('id', 'desc')->with('race')->paginate(10);
            if (!sizeof($animals)) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            foreach ($animals as $key => $value) {
                $animalLike = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $value->id)->where('like', '1')->first();
                if ($animalLike) {
                    $value['liked'] = 1;
                } else {
                    $value['liked'] = 0;
                }
            }
            $message = "Success";
            return ([
                'data' => $animals,
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
     * This function is use to upate existing animal.
     */
    public static function updateAnimal($data, $id)
    {
        $mediaLink = [];
        $media = [];
        $s3Link = 'https://' . config('filesystems.disks.s3.bucket') . '.s3' . '.amazonaws.com/';

        try {
            $animal = Animal::find($id);
            if (!$animal) {
                return ([
                    'message' => "Animal not found",
                    'code' => '',
                ]);
            }
            foreach ($data as $key => $value) {
                if ($key == "media") {
                    foreach ($value as $media_key => $media_value) {
                        if (Str::contains($media_value, $s3Link)) {
                            $mediaLink = explode($s3Link, $media_value);
                            $media[$media_key] = $mediaLink[1];
                            unset($mediaLink);
                        } else {
                            $media[$media_key] = $media_value;
                        }
                    }

                    $animal->media = json_encode($media);
                } elseif ($key == "likes" || $key == "race" || $key == "category" || $key == 'adoption_request' || $key == 'co_adoption_request') {
                    continue;
                } elseif ($key == "meta_data") {
                    $animal->$key = json_encode($value);
                } elseif ($key == "new_adoption_request" || $key == "new_co_adoption_request") {
                    continue;
                } else {
                    $animal->$key = $value;
                }
            }
            $animal->update();

            $animal = Animal::where('id', $id)->with('race')->get();

            $countanimalLike = UserLikeAnimal::where('animal_id', $id)->where('like', '1')->count();
            $countAdoptionRequest = UserAdoptionAnimal::where('animal_id', $id)->whereNull('co_owner')->count();
            $countCoAdoptionRequest = UserAdoptionAnimal::where('animal_id', $id)->where('co_owner', '1')->count();
            $animal['likes'] = $countanimalLike;
            $animal['adoption_request'] = $countAdoptionRequest;
            $animal['co_adoption_request'] = $countCoAdoptionRequest;

            $data = array(
                'animal' => $animal,
            );
            $message = "Animal updated successfully";
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
     * This function is use to show the single animal.
     */
    public static function showSingleAnimal($id)
    {
        try {
            $animal = Animal::where('id', $id)->with('seller')->with('race')->first();

            if (!$animal) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            $adoption = UserAdoptionAnimal::where('user_id', auth()->user()->id)->where('animal_id', $id)->first();
            if ($adoption) {
                $animal['user_adoption'] = $adoption;
            }
            $animalLike = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $animal->id)->where('like', '1')->first();
            if ($animalLike) {
                $animal['liked'] = 1;
            } else {
                $animal['liked'] = 0;
            }
            $data = array(
                'animal' => $animal,
            );
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

    /**
     * This function is use to delete animal.
     */
    public static function deleteAnimal($id)
    {
        try {
            $animal = Animal::where('id', $id)->where('seller_id', auth()->user()->id)->first();
            if (!$animal) {
                return ([
                    'message' => "Animal not found",
                    'code' => '',
                ]);
            }
            $notiLink = "/pet-detail/" . $animal->id;
            $notifications = CustomNotifications::where('link', $notiLink)->get();
            if (isset($notifications)) {
                foreach ($notifications as $key => $value) {
                    $value->delete();
                }
            }
            $animal->delete();
            $message = "Animal deleted successfully";
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
     * This function is use to show animals by category.
     */
    public static function showAnimalsByCategory($category_id)
    {
        try {

            $animals = Animal::where('category_id', $category_id)->where('featured', '0')->orderBy('created_at', 'desc')->with('race')->paginate(10);
            if (!sizeof($animals)) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            foreach ($animals as $key => $value) {
                $animalLike = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $value->id)->where('like', '1')->first();
                if ($animalLike) {
                    $value['liked'] = 1;
                } else {
                    $value['liked'] = 0;
                }
            }
            $data = array(
                'animals' => $animals,
            );
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

    /**
     * This function is use to show animals by seller.
     */
    public static function showAnimalsBySeller()
    {
        try {

            $animals = Animal::where('seller_id', auth()->user()->id)->orderBy('created_at', 'desc')->with('race')->get();
            if (!sizeof($animals)) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'message' => "Sorry, Animal not found",
                    'code' => '200',
                    'data' => $data,
                ]);
            }
            foreach ($animals as $key => $value) {
                $countanimalLike = UserLikeAnimal::where('animal_id', $value->id)->where('like', '1')->count();
                $countAdoptionRequest = UserAdoptionAnimal::where('animal_id', $value->id)->whereNull('co_owner')->count();
                $countCoAdoptionRequest = UserAdoptionAnimal::where('animal_id', $value->id)->where('co_owner', '1')->count();
                $value['likes'] = $countanimalLike;
                $value['adoption_request'] = $countAdoptionRequest;
                $value['co_adoption_request'] = $countCoAdoptionRequest;
                $value['new_adoption_request'] =  UserAdoptionAnimal::where('animal_id', $value->id)->whereNull('co_owner')->where('read', '0')->get();
                $value['new_co_adoption_request'] = UserAdoptionAnimal::where('animal_id', $value->id)->where('co_owner', '1')->where('read', '0')->get();
            }
            $data = array(
                'animals' => $animals,
            );
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

    /**
     * This function is use to show animals details to seller.
     */
    public static function showSellerAnimalDetail($animal_id)
    {
        try {

            $animal = Animal::where('seller_id', auth()->user()->id)->where('id', $animal_id)->with('race')->with('category')->first();
            if (!$animal) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'message' => "Sorry, Animal not found",
                    'code' => '200',
                    'data' => $data,
                ]);
            }
            $adoptionRequest = UserAdoptionAnimal::where('animal_id', $animal_id)->with('user')->get();

            $data = array(
                'animal' => $animal,
                'adoption_requests' => $adoptionRequest,
            );
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

    /**
     * This function is use to like animals.
     */
    public static function likeAnimal($animal_id)
    {
        try {
            $animal = Animal::where('id', $animal_id)->with('race')->first();
            if (!$animal) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            $dislikeAnimal = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $animal_id)->where('like', '0')->where('dislike', '1')->first();
            if ($dislikeAnimal) {
                $dislikeAnimal->delete();
            }
            $oldData = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $animal_id)->where('like', '1')->first();
            if ($oldData) {
                $oldData->delete();
                $message = "Like animal remove";
            } else {
                UserLikeAnimal::create([
                    'user_id' => auth()->user()->id,
                    'animal_id' => $animal->id,
                    'like' => 1,
                ]);
                $message = "Liked animal";
            }
            $data = array(
                'animal' => $animal,
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
     * This function is use to dislike animals.
     */
    public static function dislikeAnimal($animal_id)
    {
        try {

            $animal = Animal::where('id', $animal_id)->with('race')->first();
            if (!$animal) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            UserLikeAnimal::create([
                'user_id' => auth()->user()->id,
                'animal_id' => $animal->id,
                'dislike' => 1,
            ]);
            $data = array(
                'animal' => $animal,
            );
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
    /**
     * This function is use to like animals.
     */
    public static function showFeaturePet()
    {
        try {

            $animals = Animal::where("featured", "1")->orderBy('created_at', 'desc')->with('race')->get();
            if (!sizeof($animals)) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "Animal Not Found",
                    'code' => '200',
                ]);
            }
            foreach ($animals as $key => $value) {
                $animalLike = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $value->id)->where('like', '1')->first();
                if ($animalLike) {
                    $value['liked'] = 1;
                } else {
                    $value['liked'] = 0;
                }
            }
            $data = array(
                'animals' => $animals,
            );
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

    public static function showAnimalTinderView($pagintatePage)
    {
        $data = [];
        $animals = Animal::with('race')->orderBy('created_at', 'desc')->get();
        foreach ($animals as $key => $value) {
            $likeAnimal = UserLikeAnimal::where('user_id', auth()->user()->id)->where('animal_id', $value->id)->first();
            if (!$likeAnimal) {
                array_push($data, $value);
            }
        }
        $finalOutput = [];
        foreach ($data as $value) {
            $finalOutput[] = $value->id;
        }

        if ($pagintatePage == 0) {
            $pagintatePage = 3;
        }
        $paginatedAnimal = Animal::whereIn('id', $finalOutput)->with('race')->paginate($pagintatePage);
        $message = "Success";
        return ([
            'data' => $paginatedAnimal,
            'message' => $message,
            'code' => 200,
        ]);
    }
}
