<?php

namespace OrgModule;

use FKSDB\Components\Controls\SeriesChooser;
use FKSDB\SeriesCalculator;
use FKSDB\CoreModule\ISeriesPresenter;
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
     * @throws BadRequestException
     */
    public function getSelectedSeries() {
        $control = $this->getComponent('seriesChooser');
        if (!$control instanceof SeriesChooser) {
            throw new BadRequestException();
        }
        return $control->getSeries();
    }

    /**
     * @return SeriesChooser
     */
    public function createComponentSeriesChooser() {
        return new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest, $this->getTranslator());
    }

    /**
     * @return string
     * @throws BadRequestException
     */
    public function getSubTitle(): string {
        return parent::getSubTitle() . ' ' . sprintf(_('%s series'), $this->getSelectedSeries());
    }

}
