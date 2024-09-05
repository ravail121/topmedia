<?php

namespace App\Http\Controllers\Api\V1;

use App\CommentReplyLikes;
use App\DeviceToken;
use App\Events\Api\GamificationEvent;
use App\Followers;
use App\PostCommentLikes;
use App\PostComments;
use App\PostCommentsReply;
use App\PostLikes;
use App\Posts;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResponseController;
use App\Http\Resources\PostCommentLikesListResource;
use App\Http\Resources\PostListResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpParser\Comment;

class PostController extends ResponseController
{
    public function List(Request $request)
    {
        $this->directValidation([
            "user_id" => Rule::exists("users", "id")
        ]);

        $post = Posts::SimpleDetails()->where("user_id", "$request->user_id")
            ->limit($request->limit ?? 10)
            ->offset($request->offset ?? 0)->with(["user"])->withCount("comments_list")
            ->latest()
            ->get();

        $this->sendResponse(200, __('api.succ'), $post);
    }

    public function VideoList(Request $request)
    {
        $post = Posts::SimpleDetails()->with(["user"])->withCount("comments_list");

        if ($request->user_id) {
            $post->where("user_id", "$request->user_id");
        }

        $post = $post->where("is_video", "1")->orderBy("id", "desc")->limit($request->limit ?? 10)
            ->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __('api.succ'), $post);
    }

    public function Details($id)
    {
        $post = Posts::SimpleDetails()->with(["user"])->withCount("comments_list")->find($id);

        $post->is_following = Followers::where([
            "user_id" => Auth::id(),
            "profile_id" => $post->user_id
        ])->count();

        $this->sendResponse(200, __('api.succ'), $post);
    }

    public function create(Request $request)
    {

        $user = $request->user();

        if (!$request->description && !$request->hasFile("file")) {
            $this->sendError("Enter description or select file");
        }

        $post = Posts::create([
            "description" => $request->description ?? "",
            "user_id" => $user->id,
        ]);

        $post->update([
            "file" => $request->hasFile("file") ? implode("|", upload_multiple_file("file", "post/$post->id")) : "",
            "thumb_image" => $request->hasFile("thumb_image") ? implode("|", upload_multiple_file("thumb_image", "post/$post->id")) : "",
            "is_video" => $request->is_video == 1 ? "1" : "0"
        ]);


        $this->sendResponse(200, __('api.succ_post_create'), $post);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            "id" => ["required", Rule::exists("posts", "id")->where("user_id", $user->id)],
        ]);
        $existingPost = Posts::find($request->id);
        $existingPostFiles = $existingPost->file ? implode("|", $existingPost->file) . "|" : "";
        $existingPostThumbs = $existingPost->thumb_image ? implode("|", $existingPost->thumb_image) . "|" : "";
        Posts::where("id", $request->id)->update([
            "file" => $request->hasFile("file") ? $existingPostFiles . implode("|", upload_multiple_file("file", "post/$request->id")) : "",
            "thumb_image" => $request->hasFile("thumb_image") ? $existingPostThumbs . implode("|", upload_multiple_file("thumb_image", "post/$request->id")) : "",
            "description" => $request->description ?? "",
        ]);

        $this->sendResponse(200, __('api.succ_post_edit'), Posts::find($request->id));
    }

    public function delete($id, Request $request)
    {
        $user = $request->user();
        $find = Posts::where("user_id", $user->id)->find($id);
        if ($find) {
            $find->delete();

            $this->sendResponse(200, __('api.succ_post_delete'), []);
        }

        $this->sendError(__('api.err_invalid_post_id'));
    }

    //Add message remain from here
    public function AddComment(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            "id" => ["required", Rule::exists("posts", "id")],
            "comment" => ["required"],
        ]);

        $post = Posts::with('user')->find($request->id);

        PostComments::create([
            "user_id" => $user->id,
            "post_id" => $request->id,
            "comment" => $request->comment,
        ]);

        if ($post->user_id != Auth::id()) {
            $push_data = [
                'user_id' => $post->user_id,
                'from_user_id' => Auth::id(),
                'push_type' => 4,
                'push_title' => $user->name . " commented on your post",
                'push_message' => $user->name . " commented on your post",
                'object_id' => $post->id,
                "extra" => [
                    "name" => $user->name,
                    "profile_image" => $user->profile_image,
                ],
            ];
            send_push($post->user_id, $push_data);
            event(new GamificationEvent($post->user, 'post_comments'));
        }

        $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($request->id);

        $this->sendResponse(200, __('api.succ_comment_added'), $post);
    }

    public function EditComment(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            "id" => ["required", Rule::exists("post_comments", "id")->where("user_id", $user->id)],
            "comment" => ["required"],
        ]);

        $post_comment = PostComments::find($request->id);
        $post_comment->update([
            "comment" => $request->comment,
        ]);

        $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($post_comment->post_id);

        $this->sendResponse(200, __('api.succ_comment_edited'), $post);
    }

    public function DeleteComment($id, Request $request)
    {
        $user = $request->user();
        $find = PostComments::where("user_id", $user->id)->find($id);
        if ($find) {
            $find->delete();

            $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($find->post_id);

            $this->sendResponse(200, __('api.succ_comment_delete'), $post);
        }

        $this->sendError(__('api.err_invalid_comment_id'));
    }

    public function AddReplyComment(Request $request)
    {
        $user = $request->user();

        $this->directValidation([
            "id" => ["required", Rule::exists("post_comments", "id")],
            "comment" => ["required"],
        ]);

        $post_comment = PostComments::select("id", "post_id", "user_id")->find($request->id);

        PostCommentsReply::create([
            "post_id" => $post_comment->post_id,
            "user_id" => $user->id,
            "comment_id" => $request->id,
            "comment" => $request->comment,
        ]);

        if ($post_comment->user_id != Auth::id()) {
            $push_data = [
                'user_id' => $post_comment->user_id,
                'from_user_id' => Auth::id(),
                'push_type' => 6,
                'push_title' => $user->name . " reply on your comment",
                'push_message' => $user->name . " reply on your comment",
                'object_id' => $post_comment->id,
                "extra" => [
                    "name" => $user->name,
                    "profile_image" => $user->profile_image,
                ],
            ];
            send_push($post_comment->user_id, $push_data);
        }

        $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($post_comment->post_id);

        $this->sendResponse(200, __('api.succ_comment_reply_added'), $post);
    }

    public function EditReplyComment(Request $request)
    {
        $user = $request->user();
        $this->directValidation([
            "id" => ["required", Rule::exists("post_comments_replies", "id")->where("user_id", $user->id)],
            "comment" => ["required"],
        ]);

        $post_comment_reply = PostCommentsReply::find($request->id);

        $post_comment_reply->update([
            "comment" => $request->comment,
        ]);

        $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($post_comment_reply->post_id);

        $this->sendResponse(200, __('api.succ_comment_reply_edited'), $post);
    }

    public function DeleteReplyComment($id, Request $request)
    {
        $user = $request->user();
        $find = PostCommentsReply::where("user_id", $user->id)->find($id);
        if ($find) {
            $find->delete();

            $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($find->post_id);

            $this->sendResponse(200, __('api.succ_comment_reply_delete'), $post);
        }

        $this->sendError(__('api.err_invalid_comment_reply_id'));
    }

    public function LikePost($id, Request $request)
    {
        $user = $request->user();
        $find = Posts::SimpleDetails()->with('user')->find($id);
        if ($find) {
            $like = PostLikes::where([
                "post_id" => $find->id,
                "user_id" => $user->id,
            ])->first();

            if (!$like) {
                $find->update(["like" => $find->like + 1]);
                PostLikes::create([
                    "post_id" => $find->id,
                    "user_id" => $user->id,
                ]);

                $push_data = [
                    'user_id' => $find->user_id,
                    'from_user_id' => $user->id,
                    'push_type' => 3,
                    'push_title' => $user->name . " liked your post",
                    'push_message' => $user->name . " liked your post",
                    'object_id' => $find->id,
                    "extra" => [
                        "name" => $user->name,
                        "profile_image" => $user->profile_image,
                    ],
                ];

                if ($find->user_id != $user->id) {
                    send_push($find->user_id, $push_data);
                    event(new GamificationEvent($find->user, 'post_likes'));
                }

                $message = __('api.succ_post_like');
            } else {
                $like->delete();
                $find->update(["like" => (($find->like - 1) < 0) ? 0 : $find->like - 1]);

                $message = __('api.succ_post_unlike');
            }
            $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($id);
            $this->sendResponse(200, $message, $post);
        }

        $this->sendError(__('api.err_invalid_post_id'));
    }

    public function LikeComment($id, Request $request)
    {
        $user = $request->user();
        $find = PostComments::find($id);
        if ($find) {

            $like = PostCommentLikes::where([
                "post_id" => $find->post_id,
                "user_id" => $user->id,
                "comment_id" => $id,
            ])->first();

            if (!$like) {
                PostCommentLikes::create([
                    "post_id" => $find->post_id,
                    "user_id" => $user->id,
                    "comment_id" => $id,
                ]);

                if ($find->user_id != Auth::id()) {
                    $push_data = [
                        'user_id' => $find->user_id,
                        'from_user_id' => Auth::id(),
                        'push_type' => 5,
                        'push_title' => $user->name . " like your comment",
                        'push_message' => $user->name . " like your comment",
                        'object_id' => $find->id,
                        "extra" => [
                            "name" => $user->name,
                            "profile_image" => $user->profile_image,
                        ],
                    ];
                    send_push($find->user_id, $push_data);
                }

                $find->update(["likes" => $find->likes + 1]);

                $message = __('api.succ_comment_like');
            } else {
                $like->delete();

                $find->update(["likes" => $find->likes - 1]);

                $message = __('api.succ_comment_unlike');
            }

            $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($find->post_id);

            $this->sendResponse(200, $message, $post);
        }

        $this->sendError(__('api.err_invalid_comment_id'));
    }

    public function LikeCommentReply($id, Request $request)
    {
        $user = $request->user();
        $find = PostCommentsReply::find($id);
        if ($find) {

            $like = CommentReplyLikes::where([
                "user_id" => $user->id,
                "comment_reply_id" => $id,
            ])->first();

            if (!$like) {
                CommentReplyLikes::create([
                    "user_id" => $user->id,
                    "comment_reply_id" => $id,
                ]);

                $find->update(["likes" => $find->likes + 1]);

                $message = __('api.succ_comment_reply_like');
            } else {
                $like->delete();

                $find->update(["likes" => $find->likes - 1]);

                $message = __('api.succ_comment_reply_unlike');
            }

            $post = Posts::SimpleDetails()->with(["user", "comments_list"])->withCount("comments_list")->find($find->post_id);

            $this->sendResponse(200, $message, $post);
        }

        $this->sendError(__('api.err_invalid_comment_reply_id'));
    }


    public function GetCommentList(Request $request)
    {
        $this->directValidation([
            "post_id" => ["required", Rule::exists("posts", "id")]
        ]);

        $posts = PostComments::select(
            "id",
            "post_id",
            "user_id",
            "comment",
            "likes",
            "created_at",
            DB::raw("(select count(id) from post_comment_likes where user_id ='" . Auth::id() . "' and comment_id = `post_comments`.`id`) as liked")
        )->whereNotIn("user_id", function ($query) {
            $query->select("id")
                ->from("reported_users")
                ->where(function ($query1) {
                    $query1->where("user_id", "post_comments.user_id")
                        ->orWhere("profile_id", "post_comments.user_id");
                });
        })->with(["user", "comments_reply"])->withCount("comments_reply")->where("post_id", $request->post_id)
            ->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $posts);
    }


    public function GetCommentReplyList(Request $request)
    {
        $this->directValidation([
            "comment_id" => ["required", Rule::exists("post_comments", "id")]
        ]);

        $posts = PostCommentsReply::select("id", "comment_id", "user_id", "comment", "likes")
            ->whereNotIn("user_id", function ($query) {
                $query->select("id")
                    ->from("reported_users")
                    ->where(function ($query1) {
                        $query1->where("user_id", "post_comments_replies.user_id")
                            ->orWhere("profile_id", "post_comments_replies.user_id");
                    });
            })->with("user")->where("comment_id", $request->comment_id)
            ->latest()
            ->limit($request->limit ?? 10)->offset($request->offset ?? 10)->get();

        $this->sendResponse(200, __("api.succ"), $posts);
    }

    public function GetLikeAndCommentHistory(Request $request)
    {
        $this->directValidation([
            "user_id" => ['required', Rule::exists("users", "id")->whereNull("deleted_at")]
        ]);
        $userId = $request->user_id;
        $data = Posts::with([
            'comments_list' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->with(['comments_reply']);
            },
            'likes' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }
        ])
            ->whereHas(
                'comments_list',
                function ($query) use ($request) {
                    $query->where('user_id', $request->user_id)
                        ->orderBy('id', 'desc');
                }
            )
            ->get();
        $response = PostListResource::collection($data);
        // $posts_like = PostLikes::with("post")->where("user_id", $request->user_id)->orderBy('created_at', 'DESC')
        //     ->get();
        // $posts_comment = PostComments::with("post")->where("user_id", $request->user_id)->orderBy('created_at', 'DESC')
        //     ->get();
        // $merged = $posts_like->merge($posts_comment);
        // $sorted = $merged->sortByDesc('created_at');

        // $results = $sorted->skip($request->offset ?? 0)->take($request->limit ?? 10);

        // $return_result = [];
        // $i = 0;

        // foreach ($results as $key => $value) {
        //     $return_result[$i] = $value;
        //     if (is_a($value, \App\PostLikes::class)) {
        //         $return_result[$i]['type'] = "like";
        //     } else {
        //         $return_result[$i]['type'] = "comment";
        //     }
        //     $i++;
        // }

        $this->sendResponse(200, __("api.succ"), $response);
    }

    public function GetAllMedia(Request $request)
    {
        $this->directValidation([
            "user_id" => ['required', Rule::exists("users", "id")->whereNull("deleted_at")]
        ]);

        $posts = Posts::SimpleDetails()->where("file", "<>", "")->where("user_id", $request->user_id)
            ->whereNotNull("file")
            ->with(["user", "comments_list"])->withCount("comments_list")
            ->latest()
            ->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $posts);
    }

    public function GetNonMediaPosts(Request $request)
    {
        $this->directValidation([
            "user_id" => ['required', Rule::exists("users", "id")->whereNull("deleted_at")]
        ]);

        $posts = Posts::SimpleDetails()->where(function ($query) {
            $query->where("file", "")->orWhereNull("file");
        })->where("user_id", $request->user_id)
            ->with(["user", "comments_list"])->withCount("comments_list")
            ->latest()
            ->limit($request->limit ?? 10)->offset($request->offset ?? 0)->get();

        $this->sendResponse(200, __("api.succ"), $posts);
    }
}
