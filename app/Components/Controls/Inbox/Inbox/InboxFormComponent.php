<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\Inbox;

use FKSDB\Components\Controls\Inbox\SeriesTableFormComponent;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Models\SubmitSource;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

class InboxFormComponent extends SeriesTableFormComponent
{
    /**
     * @throws ForbiddenRequestException
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        foreach ($form->getHttpData()['submits'] as $ctId => $tasks) {
            foreach ($tasks as $taskNo => $submittedOn) {
                /** @var ContestantModel|null $contestant */
                $contestant = $this->contestYear->getContestants()->where('contestant_id', $ctId)->fetch();
                if (!$contestant) {
                    // secure check for rewrite contestant_id.
                    throw new ForbiddenRequestException();
                }
                /** @var SubmitModel|null $submit */
                $submit = $contestant->getSubmits()->where('task_id', $taskNo)->fetch();
                if (!$submittedOn && $submit) {
                    $this->flashMessage(\sprintf(_('Submit #%d deleted'), $submit->submit_id), Message::LVL_WARNING);
                    $this->submitService->disposeModel($submit);
                } elseif ($submittedOn && !$submit) {
                    $this->submitService->storeModel([
                        'task_id' => $taskNo,
                        'contestant_id' => $ctId,
                        'submitted_on' => $submittedOn,
                        'source' => SubmitSource::POST,
                    ]);
                    $this->flashMessage(
                        \sprintf(_('Submit for contestant #%d and task %d created'), $ctId, $taskNo),
                        Message::LVL_SUCCESS
                    );
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
