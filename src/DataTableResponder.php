<?php
namespace LangleyFoxall\ReactDynamicDataTableLaravelApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

/**
 * Class DataTableResponder
 * @package LangleyFoxall\ReactDynamicDataTableLaravelApi
 */
class DataTableResponder
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var int
     */
    private $perPage = 15;

    /**
     * DataTableResponder constructor.
     *
     * @param $className
     * @param Request $request
     */
    public function __construct($className, Request $request)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Provided class does not exist.');
        }

        if (!$className instanceof Model) {
            throw new \InvalidArgumentException('Provided class is not an Eloquent model.');
        }

        $this->model = new $className();
        $this->request = $request;
    }

    /**
     * Sets the number of records to return per page.
     *
     * @param int $perPage
     * @return DataTableResponder
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * Builds the Eloquent query based on the request.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery(Request $request)
    {
        $orderByField = $request->get('orderByField');
        $orderByDirection = $request->get('orderByDirection');

        $query = $this->model->query();

        if ($orderByField && $orderByDirection) {
            $query->orderBy($orderByField, $orderByDirection);
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    private function paginateQuery(Builder $query)
    {
        return $query->paginate($this->perPage);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond()
    {
        $query = $this->buildQuery($this->request);
        $results = $this->paginateQuery($query);

        return DataTableResponse::success($results)->json();
    }
}