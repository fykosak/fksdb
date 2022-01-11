<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Navigation\NavigationItemComponent;
use Fykosak\Utils\UI\Navigation\NavItem;

abstract class ChooserComponent extends BaseComponent
{
    public function render(): void
    {
        $this->template->navItem = $this->getItem();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.chooser.latte');
    }

    protected function createComponentNav(): NavigationItemComponent
    {
        return new NavigationItemComponent($this->getContext());
    }

    abstract protected function getItem(): NavItem;
}
