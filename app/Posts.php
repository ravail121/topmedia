<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Posts extends Model
{

    use SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'user_id', 'file', 'description', 'like', 'comments', 'comments_likes', 'thumb_image', 'is_video'
    ];

    public function scopeSimpleDetails($query)
    {
        return $query->select([
            "id", "file", "thumb_image", "user_id", "description", "like", "created_at", "is_video",
            DB::raw("(select count(id) from post_likes where user_id ='" . Auth::id() . "' and post_id = `posts`.`id`) as liked")
        ])->whereNotIn("id", function ($query1) {
            $query1->from("reported_posts")
                ->select("post_id")
                ->where("user_id", Auth::id() ?? 1)
                ->get();
        })->whereNotIn("id", function ($query1) {
            $query1->from("hide_posts")
                ->select("post_id")
                ->where("user_id", Auth::id() ?? 1)
                ->get();
        });
    }

    public function user()
    {
        return $this->belongsTo(new User(), "user_id")
            ->select("id", "name", "username", "profile_image")->withTrashed();
    }

    public function getFileAttribute($val)
    {
        if (
            $this->is_video == "1" && !str_contains(request()->getRequestUri(), "posts/list")
            && !str_contains(request()->getRequestUri(), "posts/all_non_media")
            && !str_contains(request()->getRequestUri(), "posts/details")
            && !str_contains(request()->getRequestUri(), "posts/like_comment")
            && !str_contains(request()->getRequestUri(), "posts/all_media")
            && !str_contains(request()->getRequestUri(), "user/home")
        ) {
            return asset($val);
        }

        $images = [];
        if ($val) {
            $files = explode("|", $val);
            // dd($files);
            if (!empty($files[0])) {
                foreach ($files as $value) {
                    $images[] = get_asset($value);
                }
            }
        }
        return $images;
    }

    public function getThumbImageAttribute($val)
    {
        if (
            $this->is_video == "1" && !str_contains(request()->getRequestUri(), "posts/list")
            && !str_contains(request()->getRequestUri(), "posts/details")
            && !str_contains(request()->getRequestUri(), "posts/all_non_media")
            && !str_contains(request()->getRequestUri(), "posts/like_comment")
            && !str_contains(request()->getRequestUri(), "posts/all_media")
            && !str_contains(request()->getRequestUri(), "user/home")
        ) {
            return asset($val);
        }

        $images = [];
        if ($val) {
            $files = explode("|", $val);
            // dd($files);
            if (!empty($files[0])) {
                foreach ($files as $value) {
                    $images[] = get_asset($value);
                }
            }
        }
        return $images;
    }

    public function comments_list()
    {
        return $this->hasMany(new PostComments(), "post_id")
            ->select(
                "id",
                "post_id",
                "user_id",
                "likes",
                "created_at",
                "comment",
                DB::raw("(select count(id) from post_comment_likes where user_id ='" . Auth::id() . "' and comment_id = `post_comments`.`id`) as liked")
            )
            ->whereNotIn("user_id", function ($query) {
                $query->select("id")
                    ->from("reported_users")
                    ->where(function ($query1) {
                        $query1->where("user_id", "post_comments.user_id")
                            ->orWhere("profile_id", "post_comments.user_id");
                    });
            })->with(["comments_reply", "user"])->withCount("comments_reply");
    }

    public function likes()
    {
        return $this->hasMany(PostLikes::class, 'post_id');
    }
}
