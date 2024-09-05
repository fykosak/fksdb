<?php

declare(strict_types=1);

namespace FKSDB\Components\Inbox\PointsForm;

use FKSDB\Components\Inbox\SeriesTableFormComponent;
use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Models\ORM\Models\SubmitModel;
use FKSDB\Models\Results\SQLResultsCache;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

class PointsFormComponent extends SeriesTableFormComponent
{
    private SQLResultsCache $resultsCache;

    final public function injectServiceSubmit(SQLResultsCache $resultsCache): void
    {
        $this->resultsCache = $resultsCache;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws \PDOException
     * @throws BadRequestException
     */
    protected function handleFormSuccess(Form $form): void
    {
        foreach ($form->getHttpData()['submits'] as $submitId => $points) {
            /** @var SubmitModel|null $submit */
            $submit = $this->submitService
                ->getForContestYear($this->contestYear, $this->series)
                ->where('submit_id', $submitId)->fetch();
            if (!$submit) {
                // secure check for rewrite submitId.
                throw new ForbiddenRequestException();
            }
            if ($points !== $submit->raw_points && $points !== '') {
                $this->submitService->storeModel(['raw_points' => +$points], $submit);
            } elseif (!is_null($submit->raw_points) && $points === '') {
                $this->submitService->storeModel(['raw_points' => null], $submit);
            }
        }
        $this->resultsCache->recalculate($this->contestYear);
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
