<?php

namespace App\Models;

use App\Models\FirebaseNotification;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostLikeComments extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'post_id',
        'like',
        'comment',
    ];
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    //This function is use to add comment to a post.
    public static function commentPost($post_id, $comment)
    {
        $post = Post::where('id', $post_id)->first();
        try {

            $post->total_comment += 1;
            $PostLikeComments = PostLikeComments::create([
                'user_id' => auth()->user()->id,
                'post_id' => $post_id,
                'comment' => $comment,
            ]);
            $post->save();
            $data = array(
                'post' => $post,
                'postLikeComment' => $PostLikeComments,
            );

            if ($post->user_id != auth()->user()->id) {
                $notificationData = [
                    "title" => "New Post Comment",
                    "body" => auth()->user()->name . " commented on your Post",
                    "screen" => "App",
                    "to_user" => $post->user_id,
                    "from_user" => auth()->user()->id,
                    "link" => "/post/" . $post->id,
                    "notification_type" => "post_like_comment",
                ];
                FirebaseNotification::sendNotification($notificationData);
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
    //This function is use to add like to a post.
    public static function likePost($post_id)
    {
        $post = Post::where('id', $post_id)->first();
        try {
            $oldPostLike = PostLikeComments::where('user_id', auth()->user()->id)->where('post_id', $post_id)->where('like', '1')->first();
            if ($oldPostLike) {
                $post->total_like -= 1;
                $oldPostLike->delete();
                $PostLikeComments = [];
                $message = "Post Like remove";

            } else {
                $post->total_like += 1;
                $PostLikeComments = PostLikeComments::create([
                    'user_id' => auth()->user()->id,
                    'post_id' => $post_id,
                    'like' => 1,
                ]);
                $message = "Post Like";

                if ($post->user_id != auth()->user()->id) {
                    $notificationData = [
                        "title" => "New Post Like",
                        "body" => auth()->user()->name . " pawed your Post",
                        "screen" => "App",
                        "to_user" => $post->user_id,
                        "from_user" => auth()->user()->id,
                        "link" => "/post/" . $post->id,
                        "notification_type" => "post_like_comment",
                    ];
                    FirebaseNotification::sendNotification($notificationData);
                }
            }
            $post->save();
            $data = array(
                'post' => $post,
                'postLikeComment' => $PostLikeComments,
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
    //This function is use to show likes and comments with user to a post.
    public static function showLikeComment($post_id)
    {
        $comments = [];
        $likes = [];
        $commentDetail = [];

        $comment = PostLikeComments::where('post_id', $post_id)->where('comment', '!=', 'null')->orderBy('id', 'desc')->get();
        $like = PostLikeComments::where('post_id', $post_id)->where('like', '1')->get();

        try {
            if (sizeof($comment)) {
                foreach ($comment as $key => $value) {
                    $user = User::where('id', $value->user_id)->first();
                    $commentDetail['comment'] = $value;
                    $commentDetail['user'] = $user;
                    array_push($comments, $commentDetail);
                }
            }
            if (sizeof($like)) {
                foreach ($comment as $key => $value) {
                    $user = User::where('id', $value->user_id)->get();
                    array_push($likes, $user);
                }
            }
            $data = array(
                'comments' => $comments,
                'likes' => $likes,
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
}
