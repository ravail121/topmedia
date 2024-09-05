<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'likes_count' => $this->likes,
            'comment' => $this->comment,
            'is_like' => $this->liked == 1,
            'replies_count' => $this->comments_reply_count,
            'replies' => CommentRepliesListResource::collection($this->comments_reply),
        ];
    }
}
