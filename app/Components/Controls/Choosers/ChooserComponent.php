<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\UI\Title;

abstract class ChooserComponent extends BaseComponent {

    protected function beforeRender(): void {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render(): void {
        $this->beforeRender();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.chooser.latte');
    }

    abstract protected function getTitle(): Title;

    abstract protected function getItems(): iterable;

    /**
     * @param mixed $item
     */
    abstract public function isItemActive($item): bool;

    /**
     * @param mixed $item
     */
    abstract public function getItemTitle($item): Title;

    /**
     * @param mixed $item
     */
    abstract public function getItemLink($item): string;

    /**
     * @param mixed $item
     */
    public function isItemVisible($item): bool {
        return true;
    }
}
