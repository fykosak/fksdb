<?php

namespace OrgModule;

use ISeriesPresenter;
use Nette\Application\UI\Form;
use SeriesCalculator;

/**
 * Presenter providing series context and a way to modify it.
 * 
 */
abstract class SeriesPresenter extends BasePresenter implements ISeriesPresenter {

    /**
     * @var int
     * @persistent
     */
    public $series;

    /**
     * @var SeriesCalculator
     */
    protected $seriesCalculator;

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        return $this->series;
    }

    //
    // ----- series choosing ----
    //
    protected function createComponentFormSelectSeries($name) {
        $form = new Form($this, $name);
        $lastSeries = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());

        $form->addSelect('series', 'Série')
                ->setItems(range(1, $lastSeries), false)
                ->setDefaultValue($this->series);

        $form->addSubmit('change', 'Změnit');
        $form->onSuccess[] = array($this, 'handleChangeSeries');
    }

    public function handleChangeSeries($form) {
        $values = $form->getValues();
        $this->series = $values['series'];
        $this->redirect('this');
    }

    protected function initSeries() {
        $session = $this->getSession()->getSection('presets');

        $defaultSeries = isset($session->defaultSeries) ? $session->defaultSeries : $this->seriesCalculator->getCurrentSeries($this->getSelectedContest());
        $lastSeries = $this->seriesCalculator->getLastSeries($this->getSelectedContest(), $this->getSelectedYear());
        $defaultSeries = min($defaultSeries, $lastSeries);

        if ($this->series === null || $this->series > $lastSeries) {
            $this->series = $defaultSeries;
        }

        // remember
        $session->defaultSeries = $this->series;
    }

    protected function startup() {
        parent::startup();
        $this->initSeries();
    }

}
