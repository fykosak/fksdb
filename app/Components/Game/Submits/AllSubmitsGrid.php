<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Grids\Components\Button\ControlButton;
use FKSDB\Components\Grids\Components\FilterGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;
use Nette\Forms\Form;

class AllSubmitsGrid extends FilterGrid
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
                'fyziklani_submit.modified',
            ]
                : [
                'fyziklani_team.name_n_id',
                'fyziklani_task.label',
                'fyziklani_submit.points',
            ]
        );
        if ($this->event->event_type_id === 1) {
            $this->addPresenterButton(':Game:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
        }
        $this->addButton(
            new ControlButton(
                $this->container,
                $this,
                new Title(null, _('Revoke')),
                fn(SubmitModel $row): array => ['revoke!', ['id' => $row->fyziklani_submit_id]],
                'btn btn-sm btn-outline-danger',
                function (SubmitModel $row): bool {
                    try {
                        $row->canRevoke();
                        return true;
                    } catch (GameException$exception) {
                        return false;
                    }
                }
            ),
            'revoke'
        );
    }

    protected function getModels(): TypedSelection
    {
        $query = $this->submitService->getTable()->where('fyziklani_team.event_id', $this->event->event_id);
        if (!isset($this->filterParams)) {
            return $query;
        }
        foreach ($this->filterParams as $key => $condition) {
            if (!$condition) {
                continue;
            }
            switch ($key) {
                case 'team':
                    $query->where('fyziklani_submit.fyziklani_team_id', $condition);
                    break;
                case 'code':
                    $codeProcessor = new TaskCodePreprocessor($this->event);
                    try {
                        $query->where(
                            'fyziklani_team_id.fyziklani_team_id =? AND fyziklani_task.fyziklani_task_id =? ',
                            $codeProcessor->getTeam($condition)->fyziklani_team_id,
                            $codeProcessor->getTask($condition)->fyziklani_task_id
                        );
                    } catch (GameException $exception) {
                        $this->flashMessage(_('Wrong task code'), Message::LVL_WARNING);
                    }
                    break;
                case 'not_null':
                    $query->where('fyziklani_submit.points IS NOT NULL');
                    break;
                case 'warnings':
                    $query->where('TIMESTAMPDIFF(SECOND,fyziklani_submit.modified,NOW()) >600')->where(
                        'fyziklani_submit.state',
                        SubmitState::NOT_CHECKED
                    );
                    break;
                case 'task':
                    $query->where('fyziklani_submit.fyziklani_task_id', $condition);
                    break;
                case 'state':
                    $query->where('fyziklani_submit.state', $condition);
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
            $handler = $this->event->createGameHandler($this->container);
            $handler->revoke($submit);
            FlashMessageDump::dump($handler->logger, $this);
            $this->redirect('this');
        } catch (GameException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->redirect('this');
        }
    }

    protected function configureForm(Form $form): void
    {
        $rows = $this->event->getPossiblyAttendingTeams();
        $teams = [];
        /** @var TeamModel2 $team */
        foreach ($rows as $team) {
            $teams[$team->fyziklani_team_id] = sprintf('(%d) %s', $team->fyziklani_team_id, $team->name);
        }

        $rows = $this->event->getTasks();
        $tasks = [];
        /** @var TaskModel $task */
        foreach ($rows as $task) {
            $tasks[$task->fyziklani_task_id] = sprintf('(%d) %s', $task->label, $task->name);
        }
        $states = [];
        foreach (SubmitState::cases() as $state) {
            $states[$state->value] = $state->label();
        }

        $form->addSelect('team', _('Team'), $teams)->setPrompt(_('--Select team--'));
        $form->addSelect('task', _('Task'), $tasks)->setPrompt(_('--Select task--'));
        $form->addText('code', _('Code'))->setHtmlAttribute('placeholder', _('Task code'));
        $form->addSelect('state', _('State'), $states)->setPrompt(_('--Select state--'));
        $form->addCheckbox('not_null', _('Only not revoked submits'));
        $form->addCheckbox('warnings', _('Show warnings'))
            ->setOption('description', _('Show non checked submits inserted more that 10 minutes ago.'));
    }
}
