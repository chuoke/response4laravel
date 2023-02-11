<?php

namespace Chuoke\Response4Laravel\Resolvers;

use Chuoke\Response4Laravel\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;

class PaginatorResolver
{
    use WithPaginationInformation;

    /**
     * @var Response
     */
    protected $responser;

    /**
     * @var AbstractPaginator|AbstractCursorPaginator
     */
    public $paginator;

    /**
     * @param Response $responser
     * @param AbstractPaginator|AbstractCursorPaginator $paginator
     */
    public function __construct(Response $responser, $paginator)
    {
        $this->responser = $responser;
        $this->paginator = $paginator;
    }

    public function resolve(?Request $request = null)
    {
        $this->responser->isCollection();

        return $this->responser->wrap(
            Arr::get($this->paginator->toArray(), 'data'),
            $this->paginationInformation($request)
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
        $paginated = $this->paginator->toArray();

        $default = $this->paginationInformationDefault($paginated);

        return $this->responser->paginationInformation($request, $this->paginator, $default);
    }
}
