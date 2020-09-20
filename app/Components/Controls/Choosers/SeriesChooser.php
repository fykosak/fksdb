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

    protected SeriesCalculator $seriesCalculator;

    private ?int $urlSeries;

    private ModelContest $contest;

    private int $year;

    private int $series;

    public function __construct(Container $container, ModelContest $contest, int $year, ?int $urlSeries) {
        parent::__construct($container);
        $this->urlSeries = $urlSeries;
        $this->contest = $contest;
        $this->year = $year;
    }

    public function injectSeriesCalculator(SeriesCalculator $seriesCalculator): void {
        $this->seriesCalculator = $seriesCalculator;
    }

    /**
     * @param bool $redirect
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    public function init(bool $redirect = true): void {
        if (!isset($this->series)) {
            $this->series = $this->selectSeries();
        }
        if ($redirect && +$this->urlSeries !== $this->series) {
            $this->getPresenter()->redirect('this', ['series' => $this->series]);
        }
    }

    /**
     * @param bool $redirect
     * @return int
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    public function getSelectedSeries(bool $redirect = true): int {
        $this->init($redirect);
        return $this->series;
    }

    /**
     * @return int
     * @throws ForbiddenRequestException
     */
    private function selectSeries(): int {
        $candidate = $this->urlSeries ?? $this->seriesCalculator->getLastSeries($this->contest, $this->year);
        if (!$this->isValidSeries($candidate)) {
            throw new ForbiddenRequestException();
        }
        return $candidate;
    }

    private function isValidSeries(?int $series): bool {
        return in_array($series, $this->getAllowedSeries());
    }

    private function getAllowedSeries(): array {
        return $this->seriesCalculator->getAllowedSeries($this->contest, $this->year);
    }

    /* ************ CHOOSER METHODS *************** */
    protected function getTitle(): Title {
        return new Title(sprintf(_('Series %d'), $this->series));
    }

    protected function getItems(): array {
        return $this->getAllowedSeries();
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
