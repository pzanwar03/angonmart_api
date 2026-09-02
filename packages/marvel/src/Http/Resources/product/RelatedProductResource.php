<?php

namespace Marvel\Http\Resources;

use Illuminate\Http\Request;

class RelatedProductResource extends Resource
{
    /**
     * Transform the resource into an array.
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
            'product_type'         => $this->product_type,
            'sale_price'           => $this->sale_price,
            'max_price'            => $this->max_price,
            'min_price'            => $this->min_price,
            'image'                => $this->image,
            'video'                => $this->video,
            'video_file'           => $this->video_file,
            'price'                => $this->price,
            'unit'                 => $this->unit,
            'is_preorder'          => (bool) $this->is_preorder,
            'preorder_available_at'=> $this->preorder_available_at,
        ];
    }
}