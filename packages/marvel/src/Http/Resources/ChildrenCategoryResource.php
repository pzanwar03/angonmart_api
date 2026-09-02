<?php

namespace Marvel\Http\Resources;

use Illuminate\Http\Request;

class ChildrenCategoryResource extends Resource
{
    /**
     * Transform the resource into an array.
     * Includes nested children recursively so the shop can render
     * category → sub-category → sub-sub-category trees.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'language'             => $this->language,
            'translated_languages' => $this->translated_languages,
            'products_count'       => $this->products_count,
            'image'                => $this->image,
            'icon'                 => $this->icon,
            // Recursive: Eloquent children() already eager-loads nested children
            'children'             => ChildrenCategoryResource::collection(
                $this->children ?? []
            ),
        ];
    }
}
