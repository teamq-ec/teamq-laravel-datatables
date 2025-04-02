<?php

namespace TeamQ\Datatables\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Adds support for using the `per_page` parameter to `spatie/laravel-query-builder` package.
 *
 * @link https://spatie.be/docs/laravel-query-builder
 *
 * @author Luis Arce
 */
trait PerPageQuery
{
    /**
     * Minimum quantity per page.
     */
    protected int $minPerPage = 1;

    /**
     * Maximum quantity per page.
     */
    protected ?int $maxPerPage = null;

    /**
     * Returns all records if the user requests it in the pagination, otherwise the requested amount
     * or the default amount if no value is sent in the `per_page` parameter.
     *
     * @return Collection|static[]|LengthAwarePaginator
     */
    public function result($perPage = null, array $columns = ['*'], string $pageName = 'page', $page = null): Collection|array|LengthAwarePaginator
    {
        $paramName = config('datatables.parameters.per_page', 'per_page');

        if ($this->isPerPageAll($paramName)) {
            return $this->get($columns);
        }

        return $this->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null): LengthAwarePaginator
    {
        $paramName = config('datatables.parameters.per_page', 'per_page');

        if ($this->validatePerPageQueryParam($paramName)) {
            $perPage = $this->request->input($paramName);
        }

        return parent::paginate($perPage, $columns, $pageName, $page)->appends($this->request->query());
    }

    /**
     * Set the minimum amount per page.
     *
     * @return $this
     */
    public function setMinPerPage(int $perPage): static
    {
        $this->minPerPage = $perPage;

        return $this;
    }

    /**
     * Set the maximum amount per page.
     *
     * @return $this
     */
    public function setMaxPerPage(int $perPage): static
    {
        $this->maxPerPage = $perPage;

        return $this;
    }

    /**
     * Check that the PerPage parameter of the query is a valid value.
     */
    protected function validatePerPageQueryParam(string $paramName): bool
    {
        if (
            ! $this->request->has($paramName) ||
            ! is_numeric($this->request->input($paramName))
        ) {
            return false;
        }

        if (
            ($this->request->input($paramName) < $this->minPerPage) ||
            ($this->maxPerPage && $this->request->input($paramName) > $this->maxPerPage)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Validates that the per page all is allowed and present in the request.
     */
    protected function isPerPageAll(string $paramName): bool
    {
        return
            $this->maxPerPage === null &&
            $this->request->query($paramName) === config('datatables.per_page.all', 'all');
    }
}
