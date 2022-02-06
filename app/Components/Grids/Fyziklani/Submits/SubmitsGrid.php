<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani\Submits;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel;
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
        $this->addColumn('label', _('Task'))->setRenderer(function ($row): string {
            $model = SubmitModel::createFromActiveRow($row); // TODO is needed?
            return $model->getFyziklaniTask()->label;
        })->setSortable(false);
    }

    /**
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function addColumnTeam(): void
    {
        $this->addJoinedColumn('e_fyziklani_team.name_n_id', function ($row): TeamModel {
            if (!$row instanceof SubmitModel) {
                $row = SubmitModel::createFromActiveRow($row);
            }
            return $row->getFyziklaniTeam();
        });
    }

    protected function getModelClassName(): string
    {
        return SubmitModel::class;
    }
}
