<?php

namespace FKSDB\Components\Grids\StoredQuery;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueriesGrid extends BaseGrid {
    /** @const No. of characters that are showed from query description. */

    public const DESCRIPTION_TRUNC = 80;

    private ServiceStoredQuery $serviceStoredQuery;

    private array $activeTagIds;

    /**
     * StoredQueries2Grid constructor.
     * @param Container $container
     * @param array $activeTagIds
     */
    public function __construct(Container $container, array $activeTagIds) {
        parent::__construct($container);
        $this->activeTagIds = $activeTagIds;
    }

    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery): void {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        if (!empty($this->activeTagIds)) {
            $queries = $this->serviceStoredQuery->findByTagType($this->activeTagIds)->order('name');
            $this->setDataSource(new NDataSource($queries));
        } else {
            $queries = $this->serviceStoredQuery->getTable()->order('name');
            $this->setDataSource(new NDataSource($queries));
        }
        $this->addColumn('query_id', _('Query Id'));
        $this->addColumn('name', _('Export name'));
        $this->addColumn('description', _('Description'))->setTruncate(self::DESCRIPTION_TRUNC);
        $this->addColumns([
            'stored_query.qid',
            'stored_query.tags',
        ]);
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function (ModelStoredQuery $row): string {
                return $this->getPresenter()->link('StoredQuery:edit', ['id' => $row->query_id]);
            });
        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function (ModelStoredQuery $row): string {
                return $this->getPresenter()->link('StoredQuery:detail', ['id' => $row->query_id]);
            });

        $this->addButton('execute', _('Execute'))
            ->setClass('btn btn-sm btn-primary')
            ->setText(_('Execute'))
            ->setLink(function (ModelStoredQuery $row): string {
                return $this->getPresenter()->link('Export:execute', ['id' => $row->query_id]);
            });
    }

    protected function getModelClassName(): string {
        return ModelStoredQuery::class;
    }
}
