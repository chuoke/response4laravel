<?php

namespace Chuoke\Response4Laravel\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

class ResourceCollectionResolver extends JsonResourceResolver
{
    public function resolve(Request $request = null)
    {
        $this->responser->isCollection();
        $this->responser->collectionDataWrap($this->wrapper(), true);

        if (
            $this->resource->resource instanceof AbstractPaginator
            || $this->resource->resource instanceof AbstractCursorPaginator
        ) {
            return (new PaginatedResourceResolver($this->responser, $this->resource))->resolve($request);
        }

        return parent::resolve($request);
    }
}
