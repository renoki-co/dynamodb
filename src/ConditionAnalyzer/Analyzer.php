<?php

namespace Rennokki\DynamoDb\ConditionAnalyzer;

use Illuminate\Support\Arr;
use Rennokki\DynamoDb\ComparisonOperator;
use Rennokki\DynamoDb\DynamoDbModel;

class Analyzer
{
    /**
     * The attached DynamoDb model.
     *
     * @var DynamoDbModel
     */
    private $model;

    /**
     * The conditions.
     *
     * @var array
     */
    private $conditions = [];

    /**
     * The index name.
     *
     * @var string
     */
    private $indexName;

    /**
     * Set the DynamoDb model.
     *
     * @param  DynamoDbModel  $model
     * @return \Rennokki\DynamoDb\ConditionAnalyzer\Analyzer
     */
    public function on(DynamoDbModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the index name.
     *
     * @param string|null  $index
     * @return \Rennokki\DynamoDb\ConditionAnalyzer\Analyzer
     */
    public function withIndex($index)
    {
        $this->indexName = $index;

        return $this;
    }

    /**
     * Set the conditions.
     *
     * @param  array  $conditions
     * @return \Rennokki\DynamoDb\ConditionAnalyzer\Analyzer
     */
    public function analyze($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Check if the search is exact.
     *
     * @return bool
     */
    public function isExactSearch(): bool
    {
        if (empty($this->conditions)) {
            return false;
        }

        if (empty($this->identifierConditions())) {
            return false;
        }

        foreach ($this->conditions as $condition) {
            if (Arr::get($condition, 'type') !== ComparisonOperator::EQ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the index.
     *
     * @return Index|null
     */
    public function index()
    {
        return $this->getIndex();
    }

    /**
     * Get the conditions for the keys.
     *
     * @return array|null
     */
    public function keyConditions()
    {
        $index = $this->getIndex();

        if ($index) {
            return $this->getConditions($index->columns());
        }

        return $this->identifierConditions();
    }

    /**
     * Filter the conditions.
     *
     * @return array
     */
    public function filterConditions(): array
    {
        $keyConditions = $this->keyConditions() ?: [];

        return array_filter($this->conditions, function ($condition) use ($keyConditions) {
            return array_search($condition, $keyConditions) === false;
        });
    }

    public function identifierConditions()
    {
        $keyNames = $this->model->getKeyNames();

        $conditions = $this->getConditions($keyNames);

        if (! $this->hasValidQueryOperator(...$keyNames)) {
            return;
        }

        return $conditions;
    }

    public function identifierConditionValues()
    {
        $idConditions = $this->identifierConditions();

        if (! $idConditions) {
            return [];
        }

        $values = [];

        foreach ($idConditions as $condition) {
            $values[$condition['column']] = $condition['value'];
        }

        return $values;
    }

    /**
     * @param $column
     *
     * @return array|null
     */
    private function getCondition($column)
    {
        return Arr::first($this->conditions, function ($condition) use ($column) {
            return $condition['column'] === $column;
        });
    }

    /**
     * @param $columns
     *
     * @return array
     */
    private function getConditions($columns): array
    {
        return array_filter($this->conditions, function ($condition) use ($columns) {
            return in_array($condition['column'], $columns);
        });
    }

    /**
     * Get the index.
     *
     * @return Index|null
     */
    private function getIndex()
    {
        if (empty($this->conditions)) {
            return;
        }

        $index = null;

        foreach ($this->model->getDynamoDbIndexKeys() as $name => $keysInfo) {
            $conditionKeys = Arr::pluck($this->conditions, 'column');
            $keys = array_values($keysInfo);

            if (count(array_intersect($conditionKeys, $keys)) === count($keys)) {
                if (! isset($this->indexName) || $this->indexName === $name) {
                    $index = new Index(
                        $name,
                        Arr::get($keysInfo, 'hash'),
                        Arr::get($keysInfo, 'range')
                    );

                    break;
                }
            }
        }

        if ($index && ! $this->hasValidQueryOperator($index->hash, $index->range)) {
            $index = null;
        }

        return $index;
    }

    /**
     * Check if the query is valid.
     *
     * @param  string  $hash
     * @param  string|null  $range
     * @return bool
     */
    private function hasValidQueryOperator($hash, $range = null): bool
    {
        $hashCondition = $this->getCondition($hash);

        $validQueryOp = ComparisonOperator::isValidQueryDynamoDbOperator($hashCondition['type']);

        if ($validQueryOp && $range) {
            $rangeCondition = $this->getCondition($range);

            $validQueryOp = ComparisonOperator::isValidQueryDynamoDbOperator(
                $rangeCondition['type'],
                true
            );
        }

        return $validQueryOp;
    }
}
