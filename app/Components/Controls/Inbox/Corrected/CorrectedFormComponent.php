<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\Corrected;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\ORM\Services\SubmitService;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class CorrectedFormComponent extends FormComponent
{
    private SubmitService $submitService;
    private ContestYearModel $contestYear;
    private int $series;

    public function __construct(Container $context, ContestYearModel $contestYear, int $series)
    {
        parent::__construct($context);
        $this->contestYear = $contestYear;
        $this->series = $series;
    }

    public function inject(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{submits:string} $values */
        $values = $form->getValues('array');
        $ids = [];
        foreach (\explode(',', $values['submits']) as $value) {
            $ids[] = trim($value);
        }
        try {
            $updated = 0;
            /** @var SubmitModel $submit */
            foreach (
                $this->submitService
                    ->getForContestYear($this->contestYear, $this->series)
                    ->where('submit_id', $ids) as $submit
            ) {
                $this->submitService->storeModel(['corrected' => 1], $submit);
                $updated++;
            }
            $this->flashMessage(\sprintf(_('Updated %d submits'), $updated), Message::LVL_INFO);
        } catch (\Throwable $exception) {
            $this->flashMessage(_('Error during updating'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.save'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addTextArea('submits', _('Submits'))->setOption('description', _('Comma separated submitIDs'));
    }
}
