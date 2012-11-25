<?php

/**
 *
 * @author michal
 */
interface IResultsModel {

    const COL_DEF_LABEL = 'label';
    const COL_DEF_LIMIT = 'limit';
    const DATA_NAME = 'name';
    const DATA_SCHOOL = 'school';
    const DATA_RANK_FROM = 'from';
    const DATA_RANK_TO = 'to';
    
    const LABEL_SUM = 'sum';
    const ALIAS_SUM = 'sum';
    const LABEL_PERCETAGE = 'percent';
    const ALIAS_PERCENTAGE = 'percent';
    
    const COL_ALIAS = 'alias';
    const DATA_PREFIX = 'd';

    public function getCategories();

    /**
     * Single series number or array of them.
     * @param mixed $series
     */
    public function setSeries($series);

    /**
     * @return mixed (see setSeries)
     */
    public function getSeries();

    public function getDataColumns();

    public function getMetaColumns();

    /**
     * @param enum $category self::CAT_*
     */
    public function getData($category);
}

?>
