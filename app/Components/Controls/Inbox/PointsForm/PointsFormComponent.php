<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\PointsForm;

use FKSDB\Components\Controls\Inbox\SeriesTableFormComponent;
use FKSDB\Components\Forms\OptimisticForm;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Services\SubmitService;
use FKSDB\Models\Submits\SeriesTable;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class PointsFormComponent extends SeriesTableFormComponent
{

    /** @var callable */
    private $invalidCacheCallback;

    private SubmitService $submitService;

    public function __construct(
        callable $invalidCacheCallback,
        Container $context,
        SeriesTable $seriesTable,
        bool $displayAll = false
    ) {
        parent::__construct($context, $seriesTable, $displayAll);
        $this->invalidCacheCallback = $invalidCacheCallback;
    }

    final public function injectServiceSubmit(SubmitService $submitService): void
    {
        $this->submitService = $submitService;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void
    {
        foreach ($form->getHttpData()['submits'] as $submitId => $points) {
            if (!$this->getSeriesTable()->getSubmits()->where('submit_id', $submitId)->fetch()) {
                // secure check for rewrite submitId.
                throw new ForbiddenRequestException();
            }
            $submit = $this->submitService->findByPrimary($submitId);
            if ($points !== '' && $points !== $submit->raw_points) {
                $this->submitService->storeModel(['raw_points' => +$points], $submit);
            } elseif (!is_null($submit->raw_points) && $points === '') {
                $this->submitService->storeModel(['raw_points' => null], $submit);
            }
        }
        ($this->invalidCacheCallback)();
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
