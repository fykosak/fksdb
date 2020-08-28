<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Components\Controls\Choosers\SeriesChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContest;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

/**
 * Class SeriesPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait SeriesPresenterTrait {

    /**
     * @var int
     * @persistent
     */
    public $series;

    /**
     * @return void
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws AbortException
     */
    protected function seriesTraitStartup(): void {
        $control = $this->getComponent('seriesChooser');
        if (!$control instanceof SeriesChooser) {
            throw new BadTypeException(SeriesChooser::class, $control);
        }
        $control->init();
    }

    /**
     * @return int
     * @throws AbortException
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function getSelectedSeries(): int {
        $control = $this->getComponent('seriesChooser');
        if (!$control instanceof SeriesChooser) {
            throw new BadTypeException(SeriesChooser::class, $control);
        }
        return $control->getSelectedSeries(false);
    }

    /**
     * @return SeriesChooser
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function createComponentSeriesChooser(): SeriesChooser {
        return new SeriesChooser($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear(), $this->series);
    }

    /**
     * @return Container
     */
    abstract protected function getContext();

    abstract public function getSelectedContest(): ?ModelContest;

    abstract public function getSelectedYear(): int;
}
