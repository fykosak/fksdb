<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Utils\Paginator;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @author      Michal Koutny (modified template path from 4a2fdc6c03)
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license    New BSD Licence
 * @link    http://addons.nette.org/cs/niftygrid
 */
class GridPaginator extends BaseComponent
{
    /** @persistent */
    public int $page = 1;
    public Paginator $paginator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->paginator = new Paginator();
    }

    public function render(): void
    {
        $this->template->paginator = $this->paginator;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.latte');
    }

    /**
     * @throws BadRequestException
     */
    public function loadState(array $params): void
    {
        parent::loadState($params);
        $this->paginator->page = $this->page;
    }
}
