<?php

namespace App\Http\Requests\Consumer\Api\Feed;

use App\Http\Requests\ResponseShape;

/**
 * @property string $media
 */
class FeedUpdateRequest extends ResponseShape
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'media' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,mp4,avi,mov',
                'max:10240',
            ],

        ];
    }
}
