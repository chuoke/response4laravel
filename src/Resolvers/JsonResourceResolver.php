<?php

namespace Chuoke\Response4Laravel\Resolvers;

use Chuoke\Response4Laravel\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JsonResourceResolver
{
    /**
     * @var Response
     */
    protected $responser;

    /**
     * The underlying resource.
     *
     * @var JsonResource
     */
    public $resource;

    public function __construct(Response $responser, JsonResource $resource)
    {
        $this->responser = $responser;
        $this->resource = $resource;
    }

    public function resolve(Request $request = null)
    {
        $this->responser->dataWrap($this->wrapper(), true);

        return $this->responser->wrap(
            $this->resource->resolve($request),
            $this->resource->with($request),
            $this->resource->additional
        );
    }

    /**
     * Get the default data wrapper for the resource.
     *
     * @return string
     */
    protected function wrapper()
    {
        return get_class($this->resource)::$wrap;
    }
}
