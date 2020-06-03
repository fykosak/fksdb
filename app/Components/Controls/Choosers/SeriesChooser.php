<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class SeriesChooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeriesChooser extends Chooser {

    private int $currentSeries;
    /**
     * @var int[]
     */
    private array $allowedSeries;

    public function setSeries(int $currentSeries, array $allowedSeries): void {
        $this->currentSeries = $currentSeries;
        $this->allowedSeries = $allowedSeries;
    }

    protected function getTitle(): Title {
        return new Title(sprintf(_('Series %d'), $this->currentSeries));
    }

    /**
     * @return int[]
     */
    protected function getItems(): array {
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
