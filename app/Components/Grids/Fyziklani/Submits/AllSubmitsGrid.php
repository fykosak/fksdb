<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani\Submits;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Fyziklani\Submit\HandlerFactory;
use FKSDB\Models\Fyziklani\Submit\TaskCodePreprocessor;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\SQL\SearchableDataSource;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class AllSubmitsGrid extends SubmitsGrid
{

    private ModelEvent $event;
    private HandlerFactory $handlerFactory;

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectPrimary(HandlerFactory $handlerFactory): void
    {
        $this->handlerFactory = $handlerFactory;
    }

    protected function getData(): IDataSource
    {
        $submits = $this->serviceFyziklaniSubmit->findAll(
            $this->event
        )/*->where('fyziklani_submit.points IS NOT NULL')*/
        ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback($this->getFilterCallBack());
        return $dataSource;
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->addColumnTeam();
        $this->addColumnTask();

        $this->addColumns([
            'fyziklani_submit.state',
            'fyziklani_submit.points',
            'fyziklani_submit.created',
        ]);
        $this->addLinkButton(':Fyziklani:Submit:edit', 'edit', _('Edit'), false, ['id' => 'fyziklani_submit_id']);
        $this->addLinkButton(':Fyziklani:Submit:detail', 'detail', _('Detail'), false, ['id' => 'fyziklani_submit_id']);

        $this->addButton('delete')
            ->setClass('btn btn-sm btn-danger')
            ->setLink(fn(ModelFyziklaniSubmit $row): string => $this->link('delete!', $row->fyziklani_submit_id))
            ->setConfirmationDialog(fn(): string => _('Really take back the task submit?'))
            ->setText(_('Delete'))
            ->setShow(fn(ModelFyziklaniSubmit $row): bool => $row->canRevoke());
    }

    private function getFilterCallBack(): callable
    {
        return function (Selection $table, array $value): void {
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
                            $table->where(
                                'e_fyziklani_team_id.e_fyziklani_team_id =? AND fyziklani_task.label =? ',
                                $teamId,
                                $taskLabel
                            );
                        } else {
                            $this->flashMessage(_('Wrong task code'), BasePresenter::FLASH_WARNING);
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
        };
    }

    public function handleDelete(int $id): void
    {
        /** @var ModelFyziklaniSubmit $submit */
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($id);
        if (!$submit) {
            $this->flashMessage(_('Submit does not exists.'), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
        try {
            $logger = new MemoryLogger();
            $handler = $this->handlerFactory->create($this->event);
            $handler->revokeSubmit($logger, $submit);
            FlashMessageDump::dump($logger, $this);
            $this->redirect('this');
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            $this->redirect('this');
        }
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        if (!$this->isSearchable()) {
            throw new InvalidStateException('Cannot create search form without searchable data source.');
        }
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->setMethod(Form::GET);

        $rows = $this->event->getPossiblyAttendingTeams();
        $teams = [];
        /** @var ModelFyziklaniTeam|ActiveRow $team */
        foreach ($rows as $team) {
            $teams[$team->e_fyziklani_team_id] = $team->name;
        }

        $rows = $this->event->getFyziklaniTasks();
        $tasks = [];
        /** @var ModelFyziklaniTask|ActiveRow $task */
        foreach ($rows as $task) {
            $tasks[$task->fyziklani_task_id] = '(' . $task->label . ') ' . $task->name;
        }

        $form->addSelect('team', _('Team'), $teams)->setPrompt(_('--Select team--'));
        $form->addSelect('task', _('Task'), $tasks)->setPrompt(_('--Select task--'));
        $form->addText('code', _('Code'))->setHtmlAttribute('placeholder', _('Task code'));
        $form->addCheckbox('not_null', _('Only not revoked submits'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form): void {
            $values = $form->getValues('array');
            $this->searchTerm = $values;
            if ($this->dataSource instanceof SearchableDataSource) {
                $this->dataSource->applyFilter($values);
            }

            // TODO is this vv needed? vv
            $count = $this->dataSource->getCount();
            $this->getPaginator()->itemCount = $count;
        };
        return $control;
    }
}
