<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;

/**
 * Class PointsFormControl
 * *
 */
class PointsFormControl extends SeriesTableFormControl {

    /** @var callable */
    private $invalidCacheCallback;

    private ServiceSubmit $serviceSubmit;

    /**
     * PointsFormControl constructor.
     * @param callable $invalidCacheCallback
     * @param Container $context
     * @param SeriesTable $seriesTable
     * @param bool $displayAll
     */
    public function __construct(callable $invalidCacheCallback, Container $context, SeriesTable $seriesTable, bool $displayAll = false) {
        parent::__construct($context, $seriesTable, $displayAll);
        $this->invalidCacheCallback = $invalidCacheCallback;
    }

    public function injectServiceSubmit(ServiceSubmit $serviceSubmit): void {
        $this->serviceSubmit = $serviceSubmit;
    }

    /**
     * @param Form $form
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    protected function handleFormSuccess(Form $form): void {
        foreach ($form->getHttpData()['submits'] as $submitId => $points) {
            if (!$this->getSeriesTable()->getSubmits()->where('submit_id', $submitId)->fetch()) {
                // secure check for rewrite submitId.
                throw new ForbiddenRequestException();
            }
            /** @var ModelSubmit $submit */
            $submit = $this->serviceSubmit->findByPrimary($submitId);
            if ($points !== "" && $points !== $submit->raw_points) {
                $this->serviceSubmit->updateModel2($submit, ['raw_points' => +$points]);
            } elseif (!is_null($submit->raw_points) && $points === "") {
                $this->serviceSubmit->updateModel2($submit, ['raw_points' => null]);
            }
        }
        ($this->invalidCacheCallback)();
        $this->redrawControl();
        $this->getPresenter()->redirect('this');
    }

    public function render(): void {
        $form = $this->getComponent('form');
        if ($form instanceof OptimisticForm) {
            $form->setDefaults();
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
