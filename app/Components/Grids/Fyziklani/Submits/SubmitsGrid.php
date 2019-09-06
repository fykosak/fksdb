<?php

namespace FKSDB\Components\Grids\Fyziklani;

use BasePresenter;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class SubmitsGrid extends BaseGrid {

    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    protected $serviceFyziklaniSubmit;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param TableReflectionFactory|null $tableReflectionFactory
     */
    public function __construct(ServiceFyziklaniSubmit $serviceFyziklaniSubmit, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnTask() {
        $this->addColumn('label', _('Task'))->setRenderer(function ($row) {
            $model = ModelFyziklaniSubmit::createFromActiveRow($row);
            return $model->getTask()->label;
        })->setSortable(false);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnTeam() {
        $this->addJoinedColumn(DbNames::TAB_E_FYZIKLANI_TEAM, 'name_n_id', function ($row) {
            if (!$row instanceof ModelFyziklaniSubmit) {
                $row = ModelFyziklaniSubmit::createFromActiveRow($row);
            }
            return $row->getTeam();
        });
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     */
    protected function addEditButton($presenter) {
        $this->addButton('edit', null)->setClass('btn btn-sm btn-warning')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Edit'))->setShow(function ($row) {
            if (!$row instanceof ModelFyziklaniSubmit) {
                $row = ModelFyziklaniSubmit::createFromActiveRow($row);
            }
            return $row->getTeam()->hasOpenSubmitting() && !is_null($row->points);
        });
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     */
    protected function addDetailButton($presenter) {
        $this->addButton('detail', null)
            ->setClass('btn btn-sm btn-primary')
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link(':Fyziklani:Submit:detail', ['id' => $row->fyziklani_submit_id]);
            })->setText(_('Detail'));
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnState() {
        $this->addReflectionColumn(DbNames::TAB_FYZIKLANI_SUBMIT, 'state', ModelFyziklaniSubmit::class);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPoints() {
        $this->addReflectionColumn(DbNames::TAB_FYZIKLANI_SUBMIT, 'points', ModelFyziklaniSubmit::class);
    }
}
