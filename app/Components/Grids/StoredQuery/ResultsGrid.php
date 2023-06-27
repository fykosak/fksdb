<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\StoredQuery;

use FKSDB\Models\StoredQuery\StoredQuery;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

/**
 * TODO prepísť z basegrid do samostatného komponentu
 */
class ResultsGrid extends BaseComponent
{

    private StoredQuery $storedQuery;

    public function __construct(StoredQuery $storedQuery, Container $container)
    {
        parent::__construct($container);
        $this->storedQuery = $storedQuery;
    }

    public function render(): void
    {
        $this->template->storedQuery = $this->storedQuery;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
    }
}
