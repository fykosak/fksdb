<?php

declare(strict_types=1);
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid\DataSource;

use Nette\Database\Table\Selection;
use NiftyGrid\FilterCondition;

class NDataSource implements IDataSource {

    private Selection $table;

    public function __construct(Selection $table) {
        $this->table = $table;
    }

    public function getData(): Selection {
        return $this->table;
    }

    public function getPrimaryKey(): ?string {
        return $this->table->getPrimary();
    }

    public function getCount(string $column = '*'): int {
        return $this->table->count($column);
    }

    public function orderData(?string $by, ?string $way): void {
        $this->table->order($by . ' ' . $way);
    }

    public function limitData(int $limit, ?int $offset = null): void {
        $this->table->limit($limit, $offset);
    }

    public function filterData(array $filters): void {
        foreach ($filters as $filter) {
            if ($filter['type'] == FilterCondition::WHERE) {
                $column = $filter['column'];
                $value = $filter['value'];
                if (isset($filter['columnFunction'])) {
                    $column = $filter['columnFunction'] . '(' . $filter['column'] . ')';
                }
                $column .= $filter['cond'];
                if (isset($filter['valueFunction'])) {
                    $column .= $filter['valueFunction'] . '(?)';
                }
                $this->table->where($column, $value);
            }
        }
    }
}
