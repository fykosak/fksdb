<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\UI\Title;

/**
 * Class Chooser
 * @package FKSDB\Components\Controls\Choosers
 */
abstract class Chooser extends BaseComponent {

    protected function beforeRender() {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render() {
        $this->beforeRender();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chooser.latte');
        $this->template->render();
    }

    abstract protected function getTitle(): Title;

    /**
     * @return array|iterable
     */
    abstract protected function getItems();

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
