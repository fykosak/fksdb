<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use Fykosak\Utils\Logging\Message;
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
                $this->flashMessage($error, Message::LVL_ERROR);
            }
        };
        $form->onSuccess[] = fn(Form $form) => $this->handleFormSuccess($form);
        return $form;
    }

    /**
     * @throws ForbiddenRequestException
     */
    abstract protected function handleFormSuccess(Form $form);
}
