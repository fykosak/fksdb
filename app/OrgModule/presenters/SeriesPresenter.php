<?php

namespace OrgModule;

use FKSDB\Components\Controls\SeriesChooser;
use ISeriesPresenter;
use Nette\Application\BadRequestException;
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


    protected function startup() {
        parent::startup();
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        return $this->series;
    }

    public function createComponentSeriesChooser($name) {
        $component = new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest, $this->translator);
        return $component;
    }

    protected function getChoosers() {
        return ['lang', 'dispatch', 'year', 'series'];
    }

}
