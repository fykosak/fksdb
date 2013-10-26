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
        if (!$this['seriesChooser']->isValid()) {
            throw new BadRequestException('Nejsou dostupné žádné série.', 500);
        }

        return $this['seriesChooser']->getSeries();
    }

    public function createComponentSeriesChooser($name) {
        $component = new SeriesChooser($this->session, $this->seriesCalculator, $this->serviceContest);
        return $component;
    }

}
