<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\Inbox;

use FKSDB\Components\Controls\Inbox\SeriesTableFormComponent;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Models\ORM\Models\SubmitSource;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class InboxFormComponent extends SeriesTableFormComponent
{
    private SubmitService $submitService;

    public function __construct(Container $context, SeriesTable $seriesTable)
    {
        parent::__construct($context, $seriesTable, true);
    }

    final public function injectServiceSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        foreach ($form->getHttpData()['submits'] as $ctId => $tasks) {
            foreach ($tasks as $taskNo => $submittedOn) {
                if (!$this->getSeriesTable()->getContestants()->where('contestant_id', $ctId)->fetch()) {
                    // secure check for rewrite contestant_id.
                    throw new ForbiddenRequestException();
                }
                $submit = $this->submitService->findByContestantId($ctId, $taskNo);
                if ($submittedOn && $submit) {
                    //   $submitService->updateModel($submit, ['submitted_on' => $submittedOn]);
// $this->flashMessage(sprintf(_('Submit #%d updated'), $submit->submit_id), I\Fykosak\Utils\Logging\Message::LVL_INFO);
                } elseif (!$submittedOn && $submit) {
                    $this->flashMessage(\sprintf(_('Submit #%d deleted'), $submit->submit_id), Message::LVL_WARNING);
                    $submit->delete();
                } elseif ($submittedOn && !$submit) {
                    $this->submitService->createNewModel([
                        'task_id' => $taskNo,
                        'contestant_id' => $ctId,
                        'submitted_on' => $submittedOn,
                        'source' => SubmitSource::POST,
                    ]);
                    $this->flashMessage(
                        \sprintf(_('Submit for contestant #%d and task %d created'), $ctId, $taskNo),
                        Message::LVL_SUCCESS
                    );
                } else {
                    // do nothing
                }
            }
        }
        $this->redrawControl();
        $this->getPresenter()->redirect('this');
    }

    final public function render(): void
    {
        $form = $this->getComponent('form');
        if ($form instanceof OptimisticForm) {
            $form->setDefaults();
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
