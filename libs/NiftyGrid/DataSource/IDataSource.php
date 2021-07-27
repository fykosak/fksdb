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

interface IDataSource {

    /**
     * Returns data
     */
    public function getData(): iterable;

    /**
     * Returns name of Primary key
     * @return string|null
     */
    public function getPrimaryKey(): ?string;

    public function getCount(string $column = "*"): int;

    public function orderData(?string $by, ?string $way): void;

    public function limitData(int $limit, ?int $offset = null): void;

    /**
     * Filter data by $filters
     * $filters = array(
     *    filter => array(
     *        column => $name
     *            - name of the column
     *
     *        type => WHERE
     *            - type of SQL condition (based on class FilterCondition - condition types)
     *
     *        datatype => TEXT|NUMERIC|DATE
     *            - data type of the column (based on class FilterCondition - filter types)
     *            - SELECT and BOOLEAN filters are translated as TEXT filter with EQUAL( = ) condition
     *
     *        cond => $condition
     *            - SQL operator ( = , > , < , LIKE ? , ...)
     *
     *        value => value for condition
     *            - the filter value (text, %text, 50, ...)
     *
     *        columnFunction => $function
     *            - SQL function for use on column (DATE, ...)
     *            - optional
     *
     *        valueFunction => $function
     *            - SQL function for use on value (DATE, ...)
     *            - optional
     *    )
     * )
     *
     * @param array $filters
     */
    public function filterData(array $filters): void;
}
