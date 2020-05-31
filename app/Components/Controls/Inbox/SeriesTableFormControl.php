<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Logging\ILogger;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;

/**
 * Class SeriesTableFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Michal Koutny
 */
abstract class SeriesTableFormControl extends SeriesTableComponent {

    public function createComponentForm(): OptimisticForm {
        $form = new OptimisticForm(
            fn() => $this->getSeriesTable()->getFingerprint(),
            fn() => $this->getSeriesTable()->formatAsFormValues()
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
    abstract protected function handleFormSuccess(Form $form): void;
}
