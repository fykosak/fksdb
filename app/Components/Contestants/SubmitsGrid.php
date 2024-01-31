<?php

declare(strict_types=1);

namespace FKSDB\Components\Contestants;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<SubmitModel,never>
 */
final class SubmitsGrid extends BaseGrid
{
    private ContestantModel $contestant;

    public function __construct(Container $container, ContestantModel $contestant)
    {
        parent::__construct($container, 1024);
        $this->contestant = $contestant;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->filtered = false;
        $this->counter = false;
        $this->addSimpleReferencedColumns([
            '@task.name',
            '@task.year',
        ]);
        $this->addTableColumn(
            new RendererItem(
                $this->container,
                fn(SubmitModel $submit) => $submit->raw_points . '/'
                    . $submit->calc_points . ' ' . _('of') . ' '
                    . $submit->task->points,
                new Title(null, _('Points'))
            ),
            'points'
        );
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->contestant->getSubmits();
    }
}
