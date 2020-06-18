<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class SeriesChooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeriesChooser extends Chooser {
    /**
     * @var int
     */
    private $currentSeries;
    /**
     * @var int[]
     */
    private $allowedSeries;

    /**
     * @param int $currentSeries
     * @param array $allowedSeries
     * @return void
     */
    public function setSeries(int $currentSeries, array $allowedSeries) {
        $this->currentSeries = $currentSeries;
        $this->allowedSeries = $allowedSeries;
    }

    protected function getTitle(): Title {
        return new Title(sprintf(_('Series %d'), $this->currentSeries));
    }

    /**
     * @return int[]
     */
    protected function getItems() {
        return $this->allowedSeries;
    }

    /**
     * @param int $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->currentSeries;
    }

    /**
     * @param int $item
     * @return string
     */
    public function getItemLabel($item): string {
        return sprintf(_('Series %d'), $item);
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
