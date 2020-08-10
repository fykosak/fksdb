<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\UI\Title;

/**
 * Class Chooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class Chooser extends BaseComponent {

    protected function beforeRender(): void {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render(): void {
        $this->beforeRender();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chooser.latte');
        $this->template->render();
    }

    abstract protected function getTitle(): Title;

    abstract protected function getItems(): iterable;

    /**
     * @param mixed $item
     * @return bool
     */
    abstract public function isItemActive($item): bool;

    /**
     * @param mixed $item
     * @return string
     */
    abstract public function getItemLabel($item): string;

    /**
     * @param mixed $item
     * @return string
     */
    abstract public function getItemLink($item): string;
}
