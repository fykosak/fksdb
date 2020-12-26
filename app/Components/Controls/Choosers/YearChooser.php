<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

class YearChooser extends Chooser {

    public const ROLE_ORG = 'org';
    public const ROLE_CONTESTANT = 'contestant';
    public const ROLE_ALL = 'all';
    public const ROLE_SELECTED = 'selected';

    private int $year;
    private array $availableYears;

    public function __construct(Container $container, int $urlYear, array $availableYears) {
        parent::__construct($container);
        $this->year = $urlYear;
        $this->availableYears = $availableYears;
    }

    protected function getTitle(): Title {
        return new Title(sprintf(_('Year %d'), $this->year));
    }

    protected function getItems(): iterable {
        return $this->availableYears;
    }

    /**
     * @param int $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->year;
    }

    /**
     * @param int $item
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title(sprintf(_('Year %d'), $item));
    }

    /**
     * @param int $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['year' => $item]);
    }
}
