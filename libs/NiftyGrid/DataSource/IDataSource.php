<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license     New BSD Licence
 * @link        http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid\DataSource;

interface IDataSource
{

    /**
     * Returns data
     */
    public function getData(): iterable;

    public function getCount(string $column = "*"): int;

    public function orderData(?string $by, ?string $way): void;

    public function limitData(int $limit, ?int $offset = null): void;
}
