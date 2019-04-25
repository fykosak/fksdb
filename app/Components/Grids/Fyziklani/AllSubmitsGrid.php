<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FyziklaniModule\BasePresenter;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class AllSubmitsGrid extends SubmitsGrid {

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->event = $event;
        parent::__construct($serviceFyziklaniSubmit);
    }

    /**
     * @param BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('Id týmu'));
        $this->addColumnTask();
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Room'));
        $this->addColumn('modified', _('Zadané'));

        $this->addButton('edit', null)->setClass('btn btn-sm btn-warning')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function (ModelFyziklaniSubmit $row) {
            return $row->getTeam()->hasOpenSubmitting() && !is_null($row->points);
        });

        $this->addButton('delete', null)->setClass('btn btn-sm btn-danger')->setLink(function ($row) {
            return $this->link('delete!', $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _('Opravdu vzít submit úlohy zpět?');
        })->setText(_('Smazat'))->setShow(function (ModelFyziklaniSubmit $row) {

            return $row->getTeam()->hasOpenSubmitting() && !is_null($row->points);
        });

        $submits = $this->serviceFyziklaniSubmit->findAll($this->event)->where('fyziklani_submit.points IS NOT NULL')
            ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name,e_fyziklani_team_id.room');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($dataSource);
    }

    /**
     * @return \Closure
     */
    private function getFilterCallBack(): \Closure {
        return function (Selection $table, $value) {
            $l = strlen($value);
            $code = str_repeat('0', 9 - $l) . strtoupper($value);
            if (TaskCodePreprocessor::checkControlNumber($code)) {
                $taskLabel = TaskCodePreprocessor::extractTaskLabel($code);
                $teamId = TaskCodePreprocessor::extractTeamId($code);
                $table->where('e_fyziklani_team_id.e_fyziklani_team_id =? AND fyziklani_task.label =? ', $teamId, $taskLabel);

            } else {
                $tokens = preg_split('/\s+/', $value);
                foreach ($tokens as $token) {
                    $table->where('e_fyziklani_team_id.name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task.label LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
                }
            }
        };
    }

    /**
     * @param $id
     */
    public function handleDelete($id) {
        $row = $this->serviceFyziklaniSubmit->findByPrimary($id);
        if (!$row) {
            $this->flashMessage(_('Submit dos not exists.'), \BasePresenter::FLASH_ERROR);
            return;
        }
        $submit = ModelFyziklaniSubmit::createFromActiveRow($row);

        if (!$submit->getTeam()->hasOpenSubmitting()) {

            $this->flashMessage('Tento tým má už uzavřené bodování', \BasePresenter::FLASH_WARNING);
            return;
        }
        $this->serviceFyziklaniSubmit->updateModel($submit, [
            'points' => null,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Submit has been deleted.'), \BasePresenter::FLASH_SUCCESS);
    }
}
