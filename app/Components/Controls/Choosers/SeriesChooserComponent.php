<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Class SeriesChooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SeriesChooserComponent extends ChooserComponent {

    private int $series;
    private array $allowedSeries;

    public function __construct(Container $container, int $series, array $allowedSeries) {
        parent::__construct($container);
        $this->series = $series;
        $this->allowedSeries = $allowedSeries;
    }

    /* ************ CHOOSER METHODS *************** */
    protected function getTitle(): Title {
        return new Title(sprintf(_('Series %d'), $this->series));
    }

    protected function getItems(): array {
        return $this->allowedSeries;
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
