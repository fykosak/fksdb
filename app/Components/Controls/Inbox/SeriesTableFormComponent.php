<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Models\Logging\Logger;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;

abstract class SeriesTableFormComponent extends SeriesTableComponent
{

    protected function createComponentForm(): OptimisticForm
    {
        $form = new OptimisticForm(
            fn(): string => $this->getSeriesTable()->getFingerprint(),
            fn(): array => $this->getSeriesTable()->formatAsFormValues()
        );
        $form->addSubmit('submit', _('Save'));
        $form->onError[] = function (Form $form) {
            foreach ($form->getErrors() as $error) {
                $this->flashMessage($error, Logger::ERROR);
            }
        };
        $form->onSuccess[] = fn(Form $form) => $this->handleFormSuccess($form);
        return $form;
    }

    /**
     * @param Form $form
     * @throws ForbiddenRequestException
     */
    abstract protected function handleFormSuccess(Form $form);
}
