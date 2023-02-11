<?php

namespace Chuoke\Response4Laravel;

use Chuoke\Response4Laravel\Resolvers\JsonResourceResolver;
use Chuoke\Response4Laravel\Resolvers\PaginatorResolver;
use Chuoke\Response4Laravel\Resolvers\ResourceCollectionResolver;
use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class Response implements Responsable
{
    /** @var int|null */
    public $status;

    /** @var string|null */
    public $message;

    /** @var mixed */
    public $data;

    /** @var string|null */
    public $dataWrap;

    /** @var string|null */
    public $collectionDataWrap;

    /** @var \Closure|null */
    public $responseUsing;

    public $isCollection = false;

    public static function make()
    {
        return new static();
    }

    public function ok($data = null, string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_OK)
            ->data($data)
            ->message($message);
    }

    public function created($data = null, string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_CREATED)
            ->data($data)
            ->message($message);
    }

    public function accepted($data = null, string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_ACCEPTED)
            ->data($data)
            ->message($message);
    }

    public function noContent($data = null, string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_NO_CONTENT)
            ->data($data)
            ->message($message);
    }

    public function badRequest(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_BAD_REQUEST)
            ->message($message);
    }

    public function unauthorized(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_UNAUTHORIZED)
            ->message($message);
    }

    public function forbidden(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_FORBIDDEN)
            ->message($message);
    }

    public function notFound(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_NOT_FOUND)
            ->message($message);
    }

    public function notAllowed(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_METHOD_NOT_ALLOWED)
            ->message($message);
    }

    public function serverError(string $message = null): static
    {
        return $this->status(HttpStatus::HTTP_INTERNAL_SERVER_ERROR)
            ->message($message);
    }

    public function calculateStatus(): int
    {
        return $this->status ?: ($this->data instanceof Model && $this->data->wasRecentlyCreated
            ? HttpStatus::HTTP_CREATED
            : HttpStatus::HTTP_OK
        );
    }

    public function toArray($request = null)
    {
        return $this->resolveData($request);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($this->responseUsing) {
            return call_user_func($this->responseUsing, $this->resolveData($request), $this->calculateStatus(), $this->message);
        }

        $data = $this->resolveData($request);

        if ($this->message) {
            $data = array_merge(['message' => $this->message], is_array($data) ? $data : ($data ? ['data' => $data] : []));
        }

        return response()
            ->json($data, $this->calculateStatus());
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function resolveData($request = null)
    {
        if ($this->data instanceof JsonResource) {
            return $this->resolveJsonResource($this->data, $request);
        } elseif ($this->data instanceof Paginator) {
            return $this->resolvePaginator($this->data, $request);
        }

        return $this->wrap($this->data);
    }

    public function resolveJsonResource($resource, Request $request = null): array
    {
        if ($resource instanceof ResourceCollection) {
            return (new ResourceCollectionResolver($this, $resource))->resolve($request);
        }

        return (new JsonResourceResolver($this, $resource))->resolve($request);
    }

    public function resolvePaginator(Paginator $paginator, Request $request = null): array
    {
        return (new PaginatorResolver($this, $paginator))->resolve($request);
    }

    /**
     * Wrap the given data if necessary.
     *
     * @param  mixed  $data
     * @param  array  $with
     * @param  array  $additional
     * @return mixed
     */
    public function wrap($data, $with = [], $additional = [])
    {
        if ($data instanceof Collection) {
            $data = $data->all();
            $this->isCollection();
        }

        if ($this->haveDefaultWrapperAndDataIsUnwrapped($data)) {
            $data = [$this->wrapper($this->isCollection) => $data];
        } elseif ($this->haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)) {
            $data = [($this->wrapper($this->isCollection) ?? 'data') => $data];
        }

        if ($with || $additional) {
            return array_merge_recursive((array) $data, $with, $additional);
        }

        return $data;
    }

    /**
     * Determine if we have a default wrapper and the given data is unwrapped.
     *
     * @param  array  $data
     * @return bool
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped($data)
    {
        return is_array($data)
            && $this->wrapper($this->isCollection)
            && !array_key_exists($this->wrapper($this->isCollection), $data);
    }

    /**
     * Determine if "with" data has been added and our data is unwrapped.
     *
     * @param  array  $data
     * @param  array  $with
     * @param  array  $additional
     * @return bool
     */
    protected function haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)
    {
        return is_array($data)
            && (!empty($with) || !empty($additional))
            && (!$this->wrapper($this->isCollection) ||
                !array_key_exists($this->wrapper($this->isCollection), $data));
    }

    /**
     * Get the default data wrapper for the resource.
     *
     * @return string|null
     */
    protected function wrapper($isCollection = false)
    {
        return $isCollection && $this->collectionDataWrap ? $this->collectionDataWrap : $this->dataWrap;
    }

    public function paginationInformation($request, $paginator, $default): array
    {
        return $default;
    }

    public function status(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function message(string $message = null): static
    {
        $this->message = $message;

        return $this;
    }

    public function data($data): static
    {
        $this->data = $data;

        return $this;
    }

    public function responseUsing(Closure $using): static
    {
        $this->responseUsing = $using;

        return $this;
    }

    public function dataWrap(string $dataWrap = null, bool $supplement = false): static
    {
        if ($supplement && $this->dataWrap) {
            return $this;
        }

        $this->dataWrap = $dataWrap;

        return $this;
    }

    public function collectionDataWrap(string $collectionDataWrap = null, bool $supplement = false): static
    {
        if ($supplement && $this->collectionDataWrap) {
            return $this;
        }

        $this->collectionDataWrap = $collectionDataWrap;

        return $this;
    }

    public function isCollection(bool $is = true): static
    {
        $this->isCollection = $is;

        return $this;
    }
}
