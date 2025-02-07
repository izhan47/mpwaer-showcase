<?php

namespace App\Http\Controllers;

use App\Helpers\ApiJsonResponseHelper;
use App\Models\UserPostBlock;
use App\Models\Post;
use App\Models\PostLikeComments;
use Illuminate\Http\Request;
use Validator;

class PostController extends Controller
{

    /**
     * Then it redirect the user to Post model to showAllPost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-all-post/{addPosition}/{perPageData}",
     * security={{"bearer_token":{}}},
     * summary="Show all posts",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="addPosition",
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="perPageData",
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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

    public function index($addPosition = 0, $perPageData = 0)
    {
        $post = new Post();
        $data = $post->showAllPost($addPosition, $perPageData);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }
    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to Post model to store function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/web/add-post",
     * security={{"bearer_token":{}}},
     * summary="Store New Post",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="details",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="file_name",
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_name' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }
        $post = new Post();
        $data = $post->store($request);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * This function is use to validate the form data enter by the user.
     * Then it redirect the user to Post model to updatePost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Post(
     * path="/api/web/update-post/{id}",
     * security={{"bearer_token":{}}},
     * summary="Update a Post",
     * tags={"Post"},
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="details",
     *       in="query",
     *       @OA\Schema(
     *          type="string"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="media",
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

    public function update(Request $request, $id)
    {
        $post = new Post();
        $data = $post->updatePost($request->all(), $id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Post model to showSinglePost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-single-post/{id}",
     * security={{"bearer_token":{}}},
     * summary="show single post",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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
    public function show($id)
    {
        $post = new Post();
        $data = $post->showSinglePost($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Post model to deletePost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Delete(
     * path="/api/web/delete-post/{id}",
     * security={{"bearer_token":{}}},
     * summary="Delete a post",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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
    public function destroy($id)
    {
        $post = new Post();
        $data = $post->deletePost($id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to PostLikeComments model to commentPost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/comment-post/{post_id}/{comment}",
     * security={{"bearer_token":{}}},
     * summary="comment a post",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="post_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
     *          )
     *       ),
     * @OA\Parameter(
     *       name="comment",
     *       in="path",
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
    public function comment($post_id, $comment)
    {
        $post = new PostLikeComments();
        $data = $post->commentPost($post_id, $comment);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to PostLikeComments model to likePost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/like-post/{post_id}",
     * security={{"bearer_token":{}}},
     * summary="Like a post ",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="post_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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
    public function like($post_id)
    {
        $post = new PostLikeComments();
        $data = $post->likePost($post_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to PostLikeComments model to showLikeComment function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-like-comment-post/{post_id}",
     * security={{"bearer_token":{}}},
     * summary="Show posts like and comments",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="post_id",
     *       required=true,
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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
    public function showLikeComment($post_id)
    {
        $post = new PostLikeComments();
        $data = $post->showLikeComment($post_id);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    /**
     * Then it redirect the user to Post model to showUserPost function.
     * Then response is redirect to the helper file  ApiJsonResponseHelper and return the response in json formate.
     */

    /**
     * @OA\Get(
     * path="/api/web/show-user-all-posts/{perPageData}",
     * security={{"bearer_token":{}}},
     * summary="Show user all posts",
     * tags={"Post"},
     * @OA\Parameter(
     *       name="perPageData",
     *       in="path",
     *       @OA\Schema(
     *          type="integer"
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

    public function showUserPost($perPageData = 0)
    {

        $post = new Post();
        $data = $post->showUserPost($perPageData);
        if ($data['code'] == 200) {
            return ApiJsonResponseHelper::successResponse($data['data'], $data['message']);
        } else {
            return ApiJsonResponseHelper::errorResponse($data['message']);
        }
    }

    public function adminPostLikeCommentDelete($post_id, $likeCommentId)
    {
        $post = Post::find($post_id);
        $postLikeComment = PostLikeComments::find($likeCommentId);
        if ($postLikeComment->like == null) {
            $post->total_comment = $post->total_comment - 1;
        } else {
            $post->total_like = $post->total_like - 1;
        }
        $post->save();
        $postLikeComment->delete();
        return redirect(url('/') . '/admin/post-like-comments');
    }


    public function blockPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'reported_detail' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }

        $post = Post::find($request->post_id);
        if (!$post) {
            return ApiJsonResponseHelper::successResponse([], 'Post not found');
        }
        $post->reported = 1;
        $post->reported_detail = $request->reported_detail;
        $post->save();

        return ApiJsonResponseHelper::successResponse([], 'Post Reported successfully');
    }

    public function blockUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'block_user' => 'required',
            'reported_detail' => 'required',
        ]);
        if ($validator->fails()) {
            return ApiJsonResponseHelper::apiValidationFailResponse($validator);
        }

        UserPostBlock::create([
            'login_user' => auth()->user()->id,
            'block_user' => $request->block_user,
            'reported_detail' => $request->reported_detail,
        ]);

        return ApiJsonResponseHelper::successResponse([], 'User Blocked successfully');
    }
}
