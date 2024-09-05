<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Gamification extends Model
{
    protected $table = "gamification";
    use SoftDeletes;

    protected $casts = [
        'min_range' => 'integer',
        'max_range' => 'integer',
        'coin' => 'string',
    ];

    public static function FindNotCompletedGamification($user)
    {

        $gamification = [
            'followers' => $user->followers->count('id'),
            'post_likes' => $user->post_likes->count('post_likes.id'),
            'post_comments' => $user->post_comments()->count('post_comments.id'),
        ];
        return Gamification::SimpleListing()->where(function ($wh) use ($gamification) {
            foreach ($gamification as $key => $value) {
                $wh_functions = function ($wh) use ($key, $value) {
                    $wh->where('relation_ship', $key)->range($value);
                };
                if ($key) {
                    $wh->orwhere($wh_functions);
                } else {
                    $wh->where($wh_functions);
                }

            }
        })->ForFresh($user)->get()->map(function ($val) use ($gamification) {
            $val->current_value = (string)($gamification[$val->relation_ship] ?? 0);
            return $val;
        });
    }

    public function scopeSimpleListing($query)
    {
        $query->select([
            "name",
            "min_range",
            "max_range",
            "coin",
            "relation_ship",
        ]);
    }

    public function scopeForFresh($query, $user)
    {
        $query->whereDoesntHave('usage', function ($usage) use ($user) {
            $usage->where('user_id', $user->id);
        });
    }

    public function usage(): HasMany
    {
        return $this->hasMany(GamificationUsage::class);
    }

    public function scopeRange($query, $value)
    {
        $query->whereraw("('{$value}' between `min_range` and `max_range`)");
    }
}
