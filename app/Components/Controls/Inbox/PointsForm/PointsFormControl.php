<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Forms\OptimisticForm;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\SeriesTable;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class PointsFormControl
 * @package FKSDB\Components\Controls\Inbox
 */
class PointsFormControl extends SeriesTableControl {
    /**
     * @var callable
     */
    private $invalidCacheCallback;

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
     */
    private function handleFormSuccess(Form $form) {
        /** @var ServiceSubmit $serviceSubmit */
        $serviceSubmit = $this->getContext()->getByType(ServiceSubmit::class);
        foreach ($form->getHttpData()['submits'] as $submitId => $points) {
            /** @var ModelSubmit $submit */
            $submit = $serviceSubmit->findByPrimary($submitId);
            if ($points !== "" && $points !== $submit->raw_points) {
                $serviceSubmit->updateModel2($submit, ['raw_points' => +$points]);
            } elseif (!is_null($submit->raw_points) && $points === "") {
                $serviceSubmit->updateModel2($submit, ['raw_points' => null]);
            }
        }
        ($this->invalidCacheCallback)();
        $this->invalidateControl();
        $this->getPresenter()->redirect('this');
    }

    public function render() {
        $form = $this->getComponent('form');
        if ($form instanceof OptimisticForm) {
            $form->setDefaults();
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
