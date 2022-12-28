<?php

declare(strict_types=1);

namespace NiftyGrid\DataSource;

use Nette\Database\Table\Selection;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
class NDataSource implements IDataSource
{
    private Selection $table;

    public function __construct(Selection $table)
    {
        $this->table = $table;
    }

    public function getData(): Selection
    {
        return $this->table;
    }

    public function getCount(string $column = '*'): int
    {
        return $this->table->count($column);
    }

    public function orderData(string $order): void
    {
        $this->table->order($order);
    }

    public function limitData(int $limit, ?int $offset = null): void
    {
        $this->table->limit($limit, $offset);
    }
}
