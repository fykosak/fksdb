<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating\AllTeams;

use FKSDB\Components\PDFGenerators\TeamSeating\SeatingPageComponent;
use Nette\DI\Container;

class PageComponent extends SeatingPageComponent
{

    private string $mode;

    public function __construct(string $mode, Container $container)
    {
        parent::__construct($container);
        $this->mode = $mode;
    }

    /**
     * @param mixed $row
     */
    final public function render($row): void
    {
        parent::render($row);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $this->mode . '.latte');
    }
}
