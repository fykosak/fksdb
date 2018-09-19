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

    /* for use in School Results */
    const LABEL_UNWEIGHTED_SUM = 'unweighted-sum';
    const ALIAS_UNWEIGHTED_SUM = 'unweighted-sum';
    const LABEL_CONTESTANTS_COUNT = 'contestants-count';
    const ALIAS_CONTESTANTS_COUNT = 'contestants-count';

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

    /**
     * @param ModelCategory $category
     */
    public function getDataColumns($category);

    public function getMetaColumns();

    /**
     * @param ModelCategory $category
     */
    public function getData($category);
}

?>
