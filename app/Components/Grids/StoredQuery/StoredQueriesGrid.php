<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\StoredQuery;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Services\StoredQuery\QueryService;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<QueryModel,array{}>
 */
class StoredQueriesGrid extends BaseGrid
{
    /** @const No. of characters that are showed from query description. */
    public const DESCRIPTION_TRUNC = 80;

    private QueryService $storedQueryService;
    /** @phpstan-var int[] */
    private array $activeTagIds;

    /**
     * @phpstan-param int[] $activeTagIds
     */
    public function __construct(Container $container, array $activeTagIds)
    {
        parent::__construct($container);
        $this->activeTagIds = $activeTagIds;
    }

    final public function injectServiceStoredQuery(QueryService $storedQueryService): void
    {
        $this->storedQueryService = $storedQueryService;
    }

    /**
     * @phpstan-return TypedSelection<QueryModel>
     */
    protected function getModels(): TypedSelection
    {
        if (count($this->activeTagIds)) {
            return $this->storedQueryService->findByTagType($this->activeTagIds)->order('name');
        } else {
            return $this->storedQueryService->getTable()->order('name');
        }
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = true;
        $this->filtered = false;
        $this->counter = true;

        $this->addSimpleReferencedColumns([
            '@stored_query.query_id',
            '@stored_query.name',
            '@stored_query.description',
            '@stored_query.qid',
            '@stored_query.tags',
        ]);

        $this->addPresenterButton(
            'StoredQuery:edit',
            'edit',
            new Title(null, _('button.edit')),
            false,
            ['id' => 'query_id'],
            'btn btn-sm btn-outline-primary'
        );
        $this->addPresenterButton(
            'StoredQuery:detail',
            'detail',
            new Title(null, _('button.detail')),
            false,
            ['id' => 'query_id'],
            'btn btn-sm btn-outline-info'
        );
        $this->addPresenterButton(
            'Export:execute',
            'execute',
            new Title(null, _('Execute export')),
            false,
            ['id' => 'query_id'],
            'btn btn-sm btn-outline-success'
        );
    }
}
