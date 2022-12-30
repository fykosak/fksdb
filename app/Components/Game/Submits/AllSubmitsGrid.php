<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\Components\FilterBaseGrid;
use FKSDB\Components\Grids\Components\Button\ControlButton;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;

class AllSubmitsGrid extends FilterBaseGrid
{
    protected SubmitService $submitService;
    protected EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServiceFyziklaniSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns(
            $this->event->event_type_id === 1
                ? [
                'fyziklani_team.name_n_id',
                'fyziklani_task.label',
                'fyziklani_submit.state',
                'fyziklani_submit.points',
                'fyziklani_submit.created',
            ]
                : [
                'fyziklani_team.name_n_id',
                'fyziklani_task.label',
                'fyziklani_submit.points',
            ]
        );
        if ($this->event->event_type_id === 1) {
            $this->addPresenterButton(':Game:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
            $this->addPresenterButton(
                ':Game:Submit:detail',
                'detail',
                _('Detail'),
                false,
                ['id' => 'fyziklani_submit_id']
            );
        }
        $this->getColumnsContainer()->getButtonContainer()->addComponent(
            new ControlButton(
                $this->container,
                $this,
                new Title(null, _('Revoke')),
                fn(SubmitModel $row): array => ['revoke!', ['id' => $row->fyziklani_submit_id]],
                'btn btn-sm btn-outline-danger',
                fn(SubmitModel $row): bool => $row->canRevoke(false)
            ),
            'revoke'
        );
    }

    protected function getModels(): Selection
    {
        $query = $this->submitService->findAll($this->event);
        foreach ($this->searchTerm as $key => $condition) {
            if (!$condition) {
                continue;
            }
            switch ($key) {
                case 'team':
                    $query->where('fyziklani_submit.fyziklani_team_id', $condition);
                    break;
                case 'code':
                    $fullCode = TaskCodePreprocessor::createFullCode($condition);
                    if (TaskCodePreprocessor::checkControlNumber($fullCode)) {
                        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
                        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);
                        $query->where(
                            'fyziklani_team_id.fyziklani_team_id =? AND fyziklani_task.label =? ',
                            $teamId,
                            $taskLabel
                        );
                    } else {
                        $this->flashMessage(_('Wrong task code'), Message::LVL_WARNING);
                    }
                    break;
                case 'not_null':
                    $query->where('fyziklani_submit.points IS NOT NULL');
                    break;
                case 'task':
                    $query->where('fyziklani_submit.fyziklani_task_id', $condition);
                    break;
            }
        }
        return $query;
    }

    public function handleRevoke(int $id): void
    {
        /** @var SubmitModel $submit */
        $submit = $this->submitService->findByPrimary($id);
        if (!$submit) {
            $this->flashMessage(_('Submit does not exists.'), Message::LVL_ERROR);
            $this->redirect('this');
        }
        try {
            $logger = new MemoryLogger();
            $handler = $this->event->createGameHandler($this->container);
            $handler->revoke($logger, $submit);
            FlashMessageDump::dump($logger, $this);
            $this->redirect('this');
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $form->setMethod(Form::GET);

        $rows = $this->event->getPossiblyAttendingTeams();
        $teams = [];
        /** @var TeamModel2 $team */
        foreach ($rows as $team) {
            $teams[$team->fyziklani_team_id] = $team->name;
        }

        $rows = $this->event->getTasks();
        $tasks = [];
        /** @var TaskModel $task */
        foreach ($rows as $task) {
            $tasks[$task->fyziklani_task_id] = '(' . $task->label . ') ' . $task->name;
        }

        $form->addSelect('team', _('Team'), $teams)->setPrompt(_('--Select team--'));
        $form->addSelect('task', _('Task'), $tasks)->setPrompt(_('--Select task--'));
        $form->addText('code', _('Code'))->setHtmlAttribute('placeholder', _('Task code'));
        $form->addCheckbox('not_null', _('Only not revoked submits'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form): void {
            $this->searchTerm = $form->getValues('array');
        };
        return $control;
    }
}
