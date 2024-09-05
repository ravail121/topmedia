<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostListResource extends JsonResource
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
            'description' => $this->description,
            'likes_count' => $this->likes->count(),
            'comments' => CommentsListResource::collection($this->comments_list),
            'comments_count' => $this->comments_list->count(),
            'is_like' => $this->like > 0,
            'images' => $this->image,
            'is_video' => $this->is_video,
        ];
    }
}
