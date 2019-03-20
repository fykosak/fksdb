<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\Database\Table\Selection;
use Nette\Forms\Form;
use Nette\InvalidStateException;
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
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->event = $event;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        parent::__construct($serviceFyziklaniSubmit);
    }

    /**
     * @param BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumn('e_fyziklani_team_id', _('Team'))->setRenderer(function (ModelFyziklaniSubmit $row) {
            return $row->getTeam()->name . ' (' . $row->getTeam()->e_fyziklani_team_id . ')';
        });
        $this->addColumnTask();
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Room'));
        $this->addColumn('modified', _('Zadané'));
        $this->addColumnState();

        $this->addButton('edit', null)->setClass('btn btn-sm btn-warning')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Edit'))->setShow(function (ModelFyziklaniSubmit $row) {
            return $row->getTeam()->hasOpenSubmitting() && !is_null($row->points);
        });

        $this->addButton('detail', null)
            ->setClass('btn btn-sm btn-primary')
            ->setLink(function ($row) use ($presenter) {
                return $presenter->link(':Fyziklani:Submit:detail', ['id' => $row->fyziklani_submit_id]);
            })->setText(_('Detail'));

        $this->addButton('delete', null)->setClass('btn btn-sm btn-danger')->setLink(function ($row) {
            return $this->link('delete!', $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _('Opravdu vzít submit úlohy zpět?');
        })->setText(_('Delete'))->setShow(function (ModelFyziklaniSubmit $row) {
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
            foreach ($value as $key => $condition) {
                if (!$condition) {
                    continue;
                }
                switch ($key) {
                    case 'team':
                        $table->where('fyziklani_submit.e_fyziklani_team_id', $condition);
                        break;
                    case 'code':
                        $fullCode = TaskCodePreprocessor::createFullCode($condition);
                        if (TaskCodePreprocessor::checkControlNumber($fullCode)) {
                            $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
                            $teamId = TaskCodePreprocessor::extractTeamId($fullCode);
                            $table->where('e_fyziklani_team_id.e_fyziklani_team_id =? AND fyziklani_task.label =? ', $teamId, $taskLabel);
                        } else {
                            $this->flashMessage(_('Zle zadaný kód úlohy'), \BasePresenter::FLASH_WARNING);
                        }
                        break;
                    case 'creator_me':
                        $personId = $this->getPresenter()->getUser()->getIdentity()->getPerson()->person_id;
                        $table->where('created_by = ? OR checked_by = ?', $personId, $personId);
                        break;
                    case 'task':
                        $table->where('fyziklani_submit.fyziklani_task_id', $condition);
                }
            }
            return;


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
        $submit = ModelFyziklaniSubmit::createFromTableRow($row);

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

    /**
     * @return FormControl
     * @throws \Nette\Application\BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }
        $control = new FormControl();
        $form = $control->getForm();
        //$form = new Form();
        $form->setMethod(Form::GET);

        $rows = $this->serviceFyziklaniTeam->findPossiblyAttending($this->event);
        $teams = [];

        foreach ($rows as $row) {
            $team = ModelFyziklaniTeam::createFromTableRow($row);
            $teams[$team->e_fyziklani_team_id] = $team->name;
        }

        $rows = $this->serviceFyziklaniTask->findAll($this->event);
        $tasks = [];
        foreach ($rows as $row) {
            $task = ModelFyziklaniTask::createFromTableRow($row);
            $tasks[$task->fyziklani_task_id] = $task->name . '(' . $task->label . ')';
        }

        $form->addSelect('team', _('Team'), $teams)->setPrompt(_('--Team--'));
        $form->addSelect('task', _('Task'), $tasks)->setPrompt(_('--task--'));
        $form->addText('code', _('Code'))->setAttribute('placeholder', _('Task code'));
        $form->addCheckbox('creator_me', _('Only my submits'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form) {
            $values = $form->getValues();
            $this->searchTerm = $values;
            $this->dataSource->applyFilter($values);
            // TODO is this vv needed? vv
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }

}
