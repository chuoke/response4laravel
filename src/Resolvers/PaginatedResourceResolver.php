<?php

namespace Chuoke\Response4Laravel\Resolvers;

use Illuminate\Http\Request;

class PaginatedResourceResolver extends JsonResourceResolver
{
    use WithPaginationInformation;

    public function resolve(?Request $request = null)
    {
        $this->responser->isCollection();
        $this->responser->collectionDataWrap($this->wrapper(), true);

        return $this->responser->wrap(
            $this->resource->resolve($request),
            array_merge_recursive(
                $this->paginationInformation($request),
                $this->resource->with($request),
                $this->resource->additional
            )
        );
    }

    /**
     * Add the pagination information to the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function paginationInformation($request)
    {
        $paginated = $this->resource->resource->toArray($request);

        $default = $this->paginationInformationDefault($paginated);

        if (method_exists($this->resource, 'paginationInformation')) {
            return $this->resource->paginationInformation($request, $paginated, $default);
        }

        return $this->responser->paginationInformation($request, $this->resource->resource, $default);
    }
}
