<?php

namespace Chuoke\Response4Laravel\Resolvers;

use Illuminate\Support\Arr;

trait WithPaginationInformation
{
    /**
     * Get the pagination links for the response.
     *
     * @param  array  $paginated
     * @return array
     */
    protected function paginationLinks($paginated)
    {
        return [
            'first' => $paginated['first_page_url'] ?? null,
            'last' => $paginated['last_page_url'] ?? null,
            'prev' => $paginated['prev_page_url'] ?? null,
            'next' => $paginated['next_page_url'] ?? null,
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param  array  $paginated
     * @return array
     */
    protected function paginationMeta($paginated)
    {
        return Arr::except($paginated, [
            'data',
            'first_page_url',
            'last_page_url',
            'prev_page_url',
            'next_page_url',
        ]);
    }

    protected function paginationInformationDefault(array $paginated): array
    {
        return [
            'links' => $this->paginationLinks($paginated),
            'meta' => $this->paginationMeta($paginated),
        ];
    }
}
