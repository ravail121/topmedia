<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentRepliesListResource extends JsonResource
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
            'comment_id' => $this->comment_id,
            'likes_count' => $this->likes,
            'is_like' => $this->liked == 1,
            'reply' => $this->comment,
            'replied_by' => new UserResource($this->user)
        ];
    }
}
