<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

abstract class SeriesTableFormComponent extends SeriesTableComponent
{
    protected function createComponentForm(): OptimisticForm
    {
        $form = new OptimisticForm(
            function (): string {
                $fingerprint = '';
                foreach ($this->submitService->getForContestYear($this->contestYear, $this->series) as $submit) {
                    /** @var SubmitModel $submit */
                    $fingerprint .= $submit->getFingerprint();
                }
                return md5($fingerprint);
            },
            /**
             * @phpstan-return array{contestant:array<int,array{submit:array<int,SubmitModel>|null}>}
             */
            function (): array {
                $submitsTable = $this->getSubmitsTable();
                $contestants = $this->contestYear->getContestants();
                $result = [];
                /** @var ContestantModel $contestant */
                foreach ($contestants as $contestant) {
                    $result[$contestant->contestant_id] = [
                        'submit' => $submitsTable[$contestant->contestant_id] ?? null,
                    ];
                }
                return [
                    'contestant' => $result,
                ];
            }
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
    abstract protected function handleFormSuccess(Form $form): void;
}
