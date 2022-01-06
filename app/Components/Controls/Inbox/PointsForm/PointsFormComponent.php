<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\PointsForm;

use FKSDB\Components\Controls\Inbox\SeriesTableFormComponent;
use FKSDB\Components\Forms\OptimisticForm;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\Services\ServiceSubmit;
use FKSDB\Models\Submits\SeriesTable;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

class PointsFormComponent extends SeriesTableFormComponent {

    /** @var callable */
    private $invalidCacheCallback;

    private ServiceSubmit $serviceSubmit;

    public function __construct(callable $invalidCacheCallback, Container $context, SeriesTable $seriesTable, bool $displayAll = false) {
        parent::__construct($context, $seriesTable, $displayAll);
        $this->invalidCacheCallback = $invalidCacheCallback;
    }

    final public function injectServiceSubmit(ServiceSubmit $serviceSubmit): void {
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws ModelException
     */
    protected function handleFormSuccess(Form $form): void {
        foreach ($form->getHttpData()['submits'] as $submitId => $points) {
            if (!$this->getSeriesTable()->getSubmits()->where('submit_id', $submitId)->fetch()) {
                // secure check for rewrite submitId.
                throw new ForbiddenRequestException();
            }
            $submit = $this->serviceSubmit->findByPrimary($submitId);
            if ($points !== '' && $points !== $submit->raw_points) {
                $this->serviceSubmit->updateModel($submit, ['raw_points' => +$points]);
            } elseif (!is_null($submit->raw_points) && $points === '') {
                $this->serviceSubmit->updateModel($submit, ['raw_points' => null]);
            }
        }
        ($this->invalidCacheCallback)();
        $this->redrawControl();
        $this->getPresenter()->redirect('this');
    }

    final public function render(): void {
        $form = $this->getComponent('form');
        if ($form instanceof OptimisticForm) {
            $form->setDefaults();
        }
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
