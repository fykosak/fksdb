<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class SubmitsGrid extends BaseGrid {

    /**
     *
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        parent::__construct();
    }

    protected function addColumnTask() {
        $this->addColumn('label', _('Úloha'))->setRenderer(function ($row) {
            $model = ModelFyziklaniSubmit::createFromTableRow($row);
            return $model->getTask()->label;
        });
    }
}
