<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Logging\ILogger;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;

/**
 * Class SeriesTableFormControl
 * @package FKSDB\Components\Controls\Inbox
 */
abstract class SeriesTableFormControl extends SeriesTableControl {
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
     * @throws ForbiddenRequestException
     */
    protected abstract function handleFormSuccess(Form $form);
}
