<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Container;

use FKSDB\Components\Grids\ListComponent\Button\ButtonGroup;
use FKSDB\Components\Grids\ListComponent\ItemComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class TableRow extends ItemComponent
{
    public function __construct(Container $container, Title $title)
    {
        parent::__construct($container, $title);
        $this->addComponent(new ButtonGroup($container, new Title(null, _('Actions'))), 'buttons');
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tableRow.latte';
    }

    public function getButtonContainer(): ButtonGroup
    {
        return $this->getComponent('buttons');
    }
}
