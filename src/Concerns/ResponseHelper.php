<?php

namespace Chuoke\Response4Laravel\Concerns;

use Chuoke\Response4Laravel\Response;

trait ResponseHelper
{
    protected function response()
    {
        return Response::make();
    }
}
