<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\UI\Title;

abstract class ChooserComponent extends BaseComponent
{

    protected function beforeRender(): void
    {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    public function render(): void
    {
        $this->beforeRender();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.chooser.latte');
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
     * @return Title
     */
    abstract public function getItemTitle($item): Title;

    /**
     * @param mixed $item
     * @return string
     */
    abstract public function getItemLink($item): string;

    /**
     * @param mixed $item
     * @return bool
     */
    public function isItemVisible($item): bool
    {
        return true;
    }
}
