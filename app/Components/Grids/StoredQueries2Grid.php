<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueries2Grid extends BaseGrid {
    /** @const No. of characters that are showed from query description. */

    const DESCRIPTION_TRUNC = 80;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @param ServiceStoredQuery $serviceStoredQuery
     * @return void
     */
    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        $queries = $this->serviceStoredQuery->getTable()->order('name');
        $this->setDataSource(new NDataSource($queries));
        $this->addColumn('query_id', _('Query Id'));
        $this->addColumn('name', _('Export name'));
        $this->addColumn('description', _('Description'))->setTruncate(self::DESCRIPTION_TRUNC);
        $this->addColumns([
            'stored_query.qid',
            'stored_query.tags',
        ]);
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function (ModelStoredQuery $row) {
                return $this->getPresenter()->link('edit', ['id' => $row->query_id]);
            });
        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function (ModelStoredQuery $row) {
                return $this->getPresenter()->link('detail', ['id' => $row->query_id]);
            });

        $this->addButton('execute', _('Execute'))
            ->setClass('btn btn-sm btn-primary')
            ->setText(_('Spustit'))
            ->setLink(function (ModelStoredQuery $row) {
                return $this->getPresenter()->link('execute', ['id' => $row->query_id]);
            });
    }

    protected function getModelClassName(): string {
        return ModelStoredQuery::class;
    }
}
