<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostComments extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    public function comments_reply()
    {
        return $this->hasMany(new PostCommentsReply(), "comment_id")
            ->select(
                "id",
                "comment_id",
                "user_id",
                "likes",
                "comment",
                DB::raw("(select count(id) from comment_reply_likes where user_id ='" . Auth::id() . "' and comment_reply_id = `post_comments_replies`.`id`) as liked")
            )
            ->whereNotIn("user_id", function ($query) {
                $query->select("id")
                    ->from("reported_users")
                    ->where(function ($query1) {
                        $query1->where("user_id", "post_comments_replies.user_id")
                            ->orWhere("profile_id", "post_comments_replies.user_id");
                    });
            })->with("user");
    }

    public function user()
    {
        return $this->belongsTo(new User(), "user_id")
            ->select("id", "name", "username", "profile_image")->withTrashed();
    }

    function post()
    {
        return $this->belongsTo(new Posts(), "post_id", "id")->with("user");
    }

    function likes()
    {
        return $this->hasMany(PostCommentLikes::class, 'comment_id', 'id');
    }
}
