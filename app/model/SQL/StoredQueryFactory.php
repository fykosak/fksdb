<?php

namespace SQL;

use BasePresenter;
use ISeriesPresenter;
use ModelStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Database\Connection;
use Nette\InvalidStateException;
use ServiceStoredQuery;
use SQL\StoredQuery;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    const PARAM_CONTEST = 'contest';
    const PARAM_YEAR = 'year';
    const PARAM_SERIES = 'series';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ISeriesPresenter
     */
    private $presenter;

    function __construct(Connection $connection, ServiceStoredQuery $serviceStoredQuery) {
        $this->connection = $connection;
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    public function getPresenter() {
        return $this->presenter;
    }

    public function setPresenter(ISeriesPresenter $presenter) {
        $this->presenter = $presenter;
    }

    public function createQuery(ModelStoredQuery $patternQuery) {
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->setContext($storedQuery);

        return $storedQuery;
    }

    public function createQueryFromSQL($sql, $parameters) {
        $patternQuery = $this->serviceStoredQuery->createNew(array(
            'sql' => $sql,
        ));

        $patternQuery->setParameters($parameters);
        $storedQuery = new StoredQuery($patternQuery, $this->connection);
        $this->setContext($storedQuery);

        return $storedQuery;
    }

    private function setContext(StoredQuery $storedQuery) {
        if (!$this->getPresenter()) {
            throw new InvalidStateException("Must provide provider of context for implicit parameters.");
        }

        $presenter = $this->getPresenter();
        $series = null;
        try {
            $series = $presenter->getSelectedSeries();
        } catch (BadRequestException $e) {
            if ($e->getCode() == 500) {
                $presenter->flashMessage(_('Kontext série pro dotazy není dostupný'), BasePresenter::FLASH_WARNING);
            } else {
                throw $e;
            }
        }
        $storedQuery->setImplicitParameters(array(
            self::PARAM_CONTEST => $presenter->getSelectedContest()->contest_id,
            self::PARAM_YEAR => $presenter->getSelectedYear(),
            self::PARAM_SERIES => $series,
        ));
    }

}
