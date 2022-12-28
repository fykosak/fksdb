<?php

declare(strict_types=1);

namespace NiftyGrid\DataSource;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */
interface IDataSource
{
    public function getData(): iterable;

    public function getCount(string $column = "*"): int;

    public function orderData(string $order): void;

    public function limitData(int $limit, int $offset): void;
}
