<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\UI\Title;

/**
 * Class Chooser
 * @package FKSDB\Components\Controls\Choosers
 */
abstract class Chooser extends BaseControl {

    protected function beforeRender() {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render() {
        $this->beforeRender();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chooser.latte');
        $this->template->render();
    }

    /**
     * @return Title
     */
    abstract protected function getTitle(): Title;

    /**
     * @return array|iterable
     */
    abstract protected function getItems();

    /**
     * @param $item
     * @return bool
     */
    abstract public function isItemActive($item): bool;

    /**
     * @param $item
     * @return string
     */
    abstract public function getItemLabel($item): string;

    /**
     * @param $item
     * @return string
     */
    abstract public function getItemLink($item): string;
}
