<?php

namespace FKSDB\Components\Grids\StoredQuery;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueriesGrid extends EntityGrid {

    /** @const No. of characters that are showed from query description. */

    public const DESCRIPTION_TRUNC = 80;

    private array $activeTagIds;

    public function __construct(Container $container, array $activeTagIds) {
        $this->activeTagIds = $activeTagIds;
        parent::__construct($container, ServiceStoredQuery::class, [
            'stored_query.query_id',
            'stored_query.name',
            'stored_query.qid',
            'stored_query.tags',
        ], count($this->activeTagIds) ? [
            ':stored_query_tag.tag_type_id' => $this->activeTagIds,
        ] : []);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->setDefaultOrder('name');

        $this->addColumn('description', _('Description'))->setTruncate(self::DESCRIPTION_TRUNC);

        $this->addLinkButton('StoredQuery:edit', 'edit', _('Edit'), false, ['id' => 'query_id']);
        $this->addLinkButton('StoredQuery:detail', 'detail', _('Detail'), false, ['id' => 'query_id']);
        $this->addLinkButton('Export:execute', 'execute', _('Execute export'), false, ['id' => 'query_id']);
    }

    protected function getModelClassName(): string {
        return ModelStoredQuery::class;
    }
}
