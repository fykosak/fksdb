<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class InboxControl extends SeriesTableControl {
    /**
     * ContestantSubmits constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context, $seriesTable, true);
        $this->monitor(IJavaScriptCollector::class);
    }

    /**
     * @return OptimisticForm
     */
    public function createComponentForm(): OptimisticForm {
        $form = new OptimisticForm(
            function () {
                return $this->getSeriesTable()->getFingerprint();
            },
            function () {
                return $this->getSeriesTable()->formatAsFormValues();
            }
        );
        $form->addSubmit('submit', _('Save'));
        $form->onError[] = function (Form $form) {
            foreach ($form->getErrors() as $error) {
                $this->flashMessage($error, ILogger::ERROR);
            }
        };
        $form->onSuccess[] = function (Form $form) {
            $this->handleFormSuccess($form);
        };
        return $form;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleFormSuccess(Form $form) {
        /** @var ServiceSubmit $serviceSubmit */
        $serviceSubmit = $this->getContext()->getByType(ServiceSubmit::class);
        foreach ($form->getHttpData()['submits'] as $ctId => $tasks) {
            foreach ($tasks as $taskNo => $submittedOn) {

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
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
