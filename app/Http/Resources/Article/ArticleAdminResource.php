<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'source' => $this->source,
            'provider' => $this->provider->value,
            'author' => $this->author,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'published_at' => $this->published_at->format('Y-m-d H:i:s'),
        ];
    }
}
