<?php

namespace App\Models;

use App\Helpers\PaginationHelper;
use App\Models\Advertisment;
use App\Models\CustomNotifications;
use App\Models\PostLikeComments;
use App\Models\UserPostBlock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \TCG\Voyager\Models\Role;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'media',
        'details',
        'total_like',
        'total_comment',
        'pin',
        'reported',
        'reported_detail',
    ];
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
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public static function store($data)
    {
        try {
            $mediaLink = [];
            $folder = config("mpawer-setting.post_media_folder");
            $post = new Post();
            $post->user_id = auth()->user()->id;
            $post->details = $data->details;
            $post->media = json_encode($data->file_name);
            $post->save();
            $data = array(
                'post' => $post,
            );
            $message = "Post addded successfully";
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
     * This function is use to show all posts.
     */
    public static function showAllPost($addPosition, $perPageData)
    {
        $result = [];
        $counter = 0;
        $addNo = 0;
        $media = [];
        $s3Link = 'https://' . config('filesystems.disks.s3.bucket') . '.s3' . '.amazonaws.com/';
        try {
            if ($addPosition == 0) {
                $addPosition = 3;
            }
            $blockUser = UserPostBlock::where('login_user', auth()->user()->id)->select('block_user')->get();
            if ($blockUser) {
                $pinPosts = Post::where('pin', '1')->where('reported', '0')->whereNotIn('user_id', $blockUser)->orderBy('created_at', 'desc')->with('user')->get();
                $simplePosts = Post::where('pin', '0')->where('reported', '0')->whereNotIn('user_id', $blockUser)->orderBy('created_at', 'desc')->with('user')->get();
            } else {
                $pinPosts = Post::where('pin', '1')->where('reported', '0')->orderBy('created_at', 'desc')->with('user')->get();
                $simplePosts = Post::where('pin', '0')->where('reported', '0')->orderBy('created_at', 'desc')->with('user')->get();
            }

            $adds = Advertisment::where('socialwall', '1')->get();
            $adds = $adds->shuffle();
            $addsCount = Advertisment::where('socialwall', '1')->count();
            $totalPinPost = 0;
            $totalSimplePosts = 0;
            if (sizeof($pinPosts)) {
                $totalPinPost = sizeof($pinPosts);
            }
            if (sizeof($simplePosts)) {
                $totalSimplePosts = sizeof($simplePosts);
            }

            $totalSize = $totalPinPost + $totalSimplePosts;
            if ($totalSize == 0) {
                $message = "No Post Found";
                return ([
                    'data' => [],
                    'message' => $message,
                    'code' => 200,
                ]);
            }
            if ($totalSize > $addPosition) {
                if (sizeof($pinPosts)) {
                    foreach ($pinPosts as $key => $value) {
                        $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $value->id)->where('like', '1')->first();
                        $value['liked'] = 0;
                        if ($postLike) {
                            $value['liked'] = 1;
                        }
                        $lastPostLike = PostLikeComments::where('post_id', $value->id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
                        $value['last_like'] = null;
                        if ($lastPostLike) {
                            $value['last_like'] = $lastPostLike->user->name;
                        }
                        $counter++;
                        array_push($result, $value);
                        if ($counter == $addPosition && isset($adds[$addNo])) {
                            array_push($result, $adds[$addNo]);
                            $addNo++;
                            if ($addsCount == $addNo) {
                                $addNo = 0;
                            }
                            $counter = 0;
                        }
                    }
                }

                if (sizeof($simplePosts)) {
                    foreach ($simplePosts as $key => $value) {
                        $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $value->id)->where('like', '1')->first();
                        $value['liked'] = 0;
                        if ($postLike) {
                            $value['liked'] = 1;
                        }
                        $lastPostLike = PostLikeComments::where('post_id', $value->id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
                        $value['last_like'] = null;
                        if ($lastPostLike) {
                            $value['last_like'] = $lastPostLike->user->name;
                        }
                        $counter++;
                        array_push($result, $value);
                        if ($counter == $addPosition && isset($adds[$addNo])) {
                            array_push($result, $adds[$addNo]);
                            $addNo++;
                            if ($addsCount == $addNo) {
                                $addNo = 0;
                            }
                            $counter = 0;
                        }
                    }
                }
            } else {
                if (sizeof($pinPosts)) {
                    foreach ($pinPosts as $key => $value) {
                        $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $value->id)->where('like', '1')->first();
                        $value['liked'] = 0;
                        if ($postLike) {
                            $value['liked'] = 1;
                        }
                        $lastPostLike = PostLikeComments::where('post_id', $value->id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
                        $value['last_like'] = null;
                        if ($lastPostLike) {
                            $value['last_like'] = $lastPostLike->user->name;
                        }
                        $counter++;
                        array_push($result, $value);
                    }
                }
                if (sizeof($simplePosts)) {
                    foreach ($simplePosts as $key => $value) {
                        $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $value->id)->where('like', '1')->first();
                        $value['liked'] = 0;
                        if ($postLike) {
                            $value['liked'] = 1;
                        }
                        $lastPostLike = PostLikeComments::where('post_id', $value->id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
                        $value['last_like'] = null;
                        if ($lastPostLike) {
                            $value['last_like'] = $lastPostLike->user->name;
                        }
                        $counter++;
                        array_push($result, $value);
                    }
                }
                if (isset($adds[$addNo])) {
                    array_push($result, $adds[$addNo]);
                    $addNo++;
                    if ($addsCount == $addNo) {
                        $addNo = 0;
                    }
                    $counter = 0;
                }
            }
            if ($perPageData == 0) {
                $perPageData = 10;
            }
            $paginated = PaginationHelper::paginate($result, $perPageData);
            $message = "Success";
            return ([
                'data' => $paginated,
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
     * This function is use to upate existing post.
     */
    public static function updatePost($data, $id)
    {
        $mediaLink = [];
        try {
            $post = Post::find($id);
            if (!$post) {
                return ([
                    'message' => "Post not found",
                    'code' => '',
                ]);
            }
            foreach ($data as $key => $value) {
                if ($key == "media") {
                    $post->media = json_encode($value);
                } else {
                    $post->$key = $value;
                }
            }
            $post->save();
            $data = array(
                'post' => $post,
            );
            $message = "post updated successfully";
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
     * This function is use to delete post.
     */
    public static function deletePost($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return ([
                    'message' => "post not found",
                    'code' => '',
                ]);
            }
            $notiLink = "/post/" . $post->id;
            $notifications = CustomNotifications::where('link', $notiLink)->get();
            if (isset($notifications)) {
                foreach ($notifications as $key => $value) {
                    $value->delete();
                }
            }
            $post->delete();
            $message = "Post deleted successfully";
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
     * This function is use to show the single post.
     */
    public static function showSinglePost($id)
    {
        try {
            $post = Post::where('id', $id)->with('user')->first();
            if (!$post) {
                return ([
                    'message' => "Post not found",
                    'code' => '',
                ]);
            }
            $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $id)->where('like', '1')->first();
            $value['liked'] = 0;
            if ($postLike) {
                $post['liked'] = 1;
            }
            $lastPostLike = PostLikeComments::where('post_id', $id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
            $value['last_like'] = null;
            if ($lastPostLike) {
                $post['last_like'] = $lastPostLike->user->name;
            }
            $data = array(
                'post' => $post,
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
     * This function is use to show the User posts.
     */
    public static function showUserPost($perPageData)
    {
        try {
            if ($perPageData == 0) {
                $perPageData = 10;
            }
            $posts = Post::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->paginate($perPageData);
            if (!sizeof($posts)) {
                $data = array(
                    'data' => [],
                );
                return ([
                    'data' => $data,
                    'message' => "No Posts",
                    'code' => '200',
                ]);
            }
            foreach ($posts as $key => $value) {
                $postLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $value->id)->where('like', '1')->first();
                $value['liked'] = 0;
                if ($postLike) {
                    $value['liked'] = 1;
                }
                $lastPostLike = PostLikeComments::where('post_id', $value->id)->where('like', '1')->orderBy('created_at', 'desc')->with('user')->first();
                $value['last_like'] = null;
                if ($lastPostLike) {
                    $value['last_like'] = $lastPostLike->user->name;
                }
            }
            $message = "Success";
            return ([
                'data' => $posts,
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
