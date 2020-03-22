<?php

namespace OrgModule;

use FKSDB\Components\Controls\SeriesChooser;
use FKSDB\SeriesCalculator;
use ISeriesPresenter;
use Nette\Application\BadRequestException;

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

    /**
     * @param SeriesCalculator $seriesCalculator
     */
    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator) {
        $this->seriesCalculator = $seriesCalculator;
    }

    protected function startup() {
        parent::startup();
        if (!$this->getComponent('seriesChooser')->isValid()) {
            throw new BadRequestException('Nejsou dostupné žádné série.', 500);
        }
    }

    /**
     * @return int
     */
    public function getSelectedSeries() {
        return $this->getComponent('seriesChooser')->getSeries();
    }

    /**
     * @param $name
     * @return SeriesChooser
     */
    public function createComponentSeriesChooser($name) {
        return new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest, $this->getTranslator());
    }

    /**
     * @return string
     */
    public function getSubTitle(): string {
        return parent::getSubTitle() . ' ' . sprintf(_('%s series'), $this->getSelectedSeries());
    }

}
