<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class InboxControl extends SeriesTableFormControl {
    /**
     * @param Form $form
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function handleFormSuccess(Form $form) {
        /** @var ServiceSubmit $serviceSubmit */
        $serviceSubmit = $this->getContext()->getByType(ServiceSubmit::class);
        foreach ($form->getHttpData()['submits'] as $ctId => $tasks) {
            foreach ($tasks as $taskNo => $submittedOn) {
                if (!$this->getSeriesTable()->getContestants()->where('ct_id', $ctId)->fetch()) {
                    // secure check for rewrite ct_id.
                    throw new ForbiddenRequestException;
                }
                $submit = $serviceSubmit->findByContestant($ctId, $taskNo);
                if ($submittedOn && $submit) {
                    //   $serviceSubmit->updateModel2($submit, ['submitted_on' => $submittedOn]);
                    //    $this->flashMessage(sprintf(_('Submit #%d updated'), $submit->submit_id), ILogger::INFO);
                } elseif (!$submittedOn && $submit) {
                    $this->flashMessage(\sprintf(_('Submit #%d deleted'), $submit->submit_id), ILogger::WARNING);
                    $submit->delete();
                } elseif ($submittedOn && !$submit) {
                    $serviceSubmit->createNewModel([
                        'task_id' => $taskNo,
                        'ct_id' => $ctId,
                        'submitted_on' => $submittedOn,
                        'source' => ModelSubmit::SOURCE_POST,
                    ]);
                    $this->flashMessage(\sprintf(_('Submit for contestant #%d and task %d created'), $ctId, $taskNo), ILogger::SUCCESS);
                } else {
                    // do nothing
                }
            }
        }
        $this->invalidateControl();
        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $form = $this->getComponent('form');
        if ($form instanceof OptimisticForm) {
            $form->setDefaults();
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
