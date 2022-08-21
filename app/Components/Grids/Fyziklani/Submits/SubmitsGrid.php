<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani\Submits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use NiftyGrid\DuplicateColumnException;

abstract class SubmitsGrid extends BaseGrid
{

    protected SubmitService $submitService;

    final public function injectServiceFyziklaniSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnTask(): void
    {
        $this->addColumn('label', _('Task'))->setRenderer(
            fn(SubmitModel $model): string => $model->fyziklani_task->label
        )->setSortable(false);
    }

    /**
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function addColumnTeam(): void
    {
        $this->addJoinedColumn('fyziklani_team.name_n_id', fn(SubmitModel $row): TeamModel2 => $row->fyziklani_team);
    }
}
