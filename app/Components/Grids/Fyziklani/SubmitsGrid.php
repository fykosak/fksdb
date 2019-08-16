<?php

namespace FKSDB\Components\Grids\Fyziklani;

use BasePresenter;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use Nette\Utils\Html;


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
    public function __construct(ServiceFyziklaniSubmit $serviceFyziklaniSubmit, TableReflectionFactory $tableReflectionFactory = null) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnTask() {
        $this->addColumn('label', _('Task'))->setRenderer(function ($row) {
            $model = ModelFyziklaniSubmit::createFromActiveRow($row);
            return $model->getTask()->label;
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnTeam() {
        $this->addJoinedColumn('e_fyziklani_team', 'name_n_id', function ($row) {
            if (!$row instanceof ModelFyziklaniSubmit) {
                $row = ModelFyziklaniSubmit::createFromActiveRow($row);
            }
            return $row->getTeam();
        });
    }

    /**
     * @param BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
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
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function addDetailButton($presenter) {
        $this->addButton('detail', null)
            ->setClass('btn btn-sm btn-primary')
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link(':Fyziklani:Submit:detail', ['id' => $row->fyziklani_submit_id]);
            })->setText(_('Detail'));
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnState() {
        // $this->addReflectionColumn('fyziklani_submit','state',ModelFyziklaniSubmit::class);
        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelFyziklaniSubmit::createFromActiveRow($row);
            switch ($model->state) {
                case ModelFyziklaniSubmit::STATE_CHECKED:
                    return Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('checked'));
                default:
                case ModelFyziklaniSubmit::STATE_NOT_CHECKED:
                    return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText(_('not checked'));
            }
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnPoints() {
        // $this->addReflectionColumn('fyziklani_submit', 'points', ModelFyziklaniSubmit::class);
    }
}
