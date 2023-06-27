<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Utils\Paginator as NettePaginator;

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @author      Michal Koutny (modified template path from 4a2fdc6c03)
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license    New BSD Licence
 * @link    http://addons.nette.org/cs/niftygrid
 */
class Paginator extends BaseComponent
{
    /** @persistent */
    public int $page = 1;
    public NettePaginator $paginator;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->paginator = new NettePaginator();
        $this->paginator->setItemsPerPage(20);
    }

    public function render(): void
    {
        $page = $this->paginator->page;
        if ($this->paginator->getPageCount() < 2) {
            $steps = [$page];
        } else {
            $arr = range(
                max($this->paginator->getFirstPage(), $page - 3),
                min($this->paginator->getLastPage(), $page + 3)
            );
            $count = 4;
            $quotient = ($this->paginator->getPageCount() - 1) / $count;
            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $this->paginator->getFirstPage();
            }
            sort($arr);
            $steps = array_values(array_unique($arr));
        }
        $this->template->steps = $steps;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'paginator.latte');
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
