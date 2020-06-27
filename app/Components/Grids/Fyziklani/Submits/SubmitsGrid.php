<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class SubmitsGrid extends BaseGrid {

    /**
     * @var ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @return void
     */
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnTask() {
        $this->addColumn('label', _('Task'))->setRenderer(function ($row) {
            $model = ModelFyziklaniSubmit::createFromActiveRow($row); // TODO is needed?
            return $model->getFyziklaniTask()->label;
        })->setSortable(false);
    }

    /**
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function addColumnTeam() {
        $this->addJoinedColumn('e_fyziklani_team.name_n_id', function ($row) {
            if (!$row instanceof ModelFyziklaniSubmit) {
                $row = ModelFyziklaniSubmit::createFromActiveRow($row);  // TODO is needed?
            }
            return $row->getFyziklaniTeam();
        });
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniSubmit::class;
    }
}
