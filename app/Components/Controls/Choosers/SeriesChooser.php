<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\SeriesCalculator;
use FKSDB\UI\Title;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Class SeriesChooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeriesChooser extends Chooser {

    private SeriesCalculator $seriesCalculator;
    private ModelContest $contest;
    private int $year;
    private int $series;

    public function __construct(Container $container, ModelContest $contest, int $year, int $series) {
        parent::__construct($container);
        $this->series = $series;
        $this->contest = $contest;
        $this->year = $year;
    }

    final public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
        $this->seriesCalculator = $seriesCalculator;
    }

    /* ************ CHOOSER METHODS *************** */
    protected function getTitle(): Title {
        return new Title(sprintf(_('Series %d'), $this->series));
    }

    protected function getItems(): array {
        return $this->seriesCalculator->getAllowedSeries($this->contest, $this->year);
    }

    /**
     * @param int $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->series;
    }

    /**
     * @param int $item
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title(sprintf(_('Series %d'), $item));
    }

    /**
     * @param int $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['series' => $item]);
    }
}
