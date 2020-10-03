<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class SubmitsGrid extends BaseGrid {

    protected ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    final public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnTask(): void {
        $this->addColumn('label', _('Task'))->setRenderer(function ($row): string {
            $model = ModelFyziklaniSubmit::createFromActiveRow($row); // TODO is needed?
            return $model->getFyziklaniTask()->label;
        })->setSortable(false);
    }

    /**
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function addColumnTeam(): void {
        $this->addJoinedColumn('e_fyziklani_team.name_n_id', function ($row): ModelFyziklaniTeam {
            if (!$row instanceof ModelFyziklaniSubmit) {
                $row = ModelFyziklaniSubmit::createFromActiveRow($row);
            }
            return $row->getFyziklaniTeam();
        });
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniSubmit::class;
    }
}
