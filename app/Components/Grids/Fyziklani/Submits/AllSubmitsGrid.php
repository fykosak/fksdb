<?php

namespace FKSDB\Components\Grids\Fyziklani;

use Closure;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Fyziklani\TaskCodePreprocessor;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use SQL\SearchableDataSource;

/**
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
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    public function injectPrimary(ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumns(['e_fyziklani_team.name_n_id']);
        $this->addColumnTask();

        $this->addColumns([
            'fyziklani_submit.state',
            'fyziklani_submit.points',
            'fyziklani_submit.created',
        ]);
        $this->addLinkButton(':Fyziklani:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
        $this->addLinkButton(':Fyziklani:Submit:detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_submit_id']);

        $this->addButton('delete', null)
            ->setClass('btn btn-sm btn-danger')
            ->setLink(function (ModelFyziklaniSubmit $row) {
                return $this->link('delete!', $row->fyziklani_submit_id);
            })->setConfirmationDialog(function () {
                return _('Opravdu vzít submit úlohy zpět?');
            })->setText(_('Delete'))
            ->setShow(function (ModelFyziklaniSubmit $row) {
                return $row->canRevoke();
            });
        $submits = $this->serviceFyziklaniSubmit->findAll($this->event)/*->where('fyziklani_submit.points IS NOT NULL')*/
        ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($dataSource);
    }

    /**
     * @return Closure
     */
    private function getFilterCallBack(): callable {
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
                            $this->flashMessage(_('Wrong task code'), \BasePresenter::FLASH_WARNING);
                        }
                        break;
                    case 'not_null':
                        $table->where('fyziklani_submit.points IS NOT NULL');
                        break;
                    case 'task':
                        $table->where('fyziklani_submit.fyziklani_task_id', $condition);
                        break;
                }
            }
            return;
        };
    }

    /**
     * @param $id
     * @throws AbortException
     */
    public function handleDelete($id) {
        /**
         * @var ModelFyziklaniSubmit $submit
         */
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($id);
        if (!$submit) {
            $this->flashMessage(_('Submit dos not exists.'), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
            return;
        }
        try {
            $log = $this->serviceFyziklaniSubmit->revokeSubmit($submit, $this->getPresenter()->getUser());
            $this->flashMessage($log->getMessage(), \BasePresenter::FLASH_SUCCESS);
            $this->redirect('this');
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentSearchForm(): FormControl {
        if (!$this->isSearchable()) {
            throw new InvalidStateException("Cannot create search form without searchable data source.");
        }
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->setMethod(Form::GET);

        $rows = $this->serviceFyziklaniTeam->findPossiblyAttending($this->event);
        $teams = [];
        /** @var ModelFyziklaniTeam $team */
        foreach ($rows as $team) {
            $teams[$team->e_fyziklani_team_id] = $team->name;
        }

        $rows = $this->serviceFyziklaniTask->findAll($this->event);
        $tasks = [];
        /** @var ModelFyziklaniTask $task */
        foreach ($rows as $task) {
            $tasks[$task->fyziklani_task_id] = '(' . $task->label . ') ' . $task->name;
        }

        $form->addSelect('team', _('Team'), $teams)->setPrompt(_('--Select team--'));
        $form->addSelect('task', _('Task'), $tasks)->setPrompt(_('--Select task--'));
        $form->addText('code', _('Code'))->setAttribute('placeholder', _('Task code'));
        $form->addCheckbox('not_null', _('Only not revoked submits'));
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
