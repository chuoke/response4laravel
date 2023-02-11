<?php

namespace Chuoke\Response4Laravel\Tests\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResourceWithoutWrap extends JsonResource
{
    public static $wrap = null;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
