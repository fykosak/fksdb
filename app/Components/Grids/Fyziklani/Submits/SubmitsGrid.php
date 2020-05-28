<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
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
     * @throws DuplicateColumnException
     */
    protected function addColumnTeam() {
        $this->addJoinedColumn(DbNames::TAB_E_FYZIKLANI_TEAM, 'name_n_id', function ($row) {
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
