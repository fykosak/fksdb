<?php

declare(strict_types=1);

namespace NiftyGrid;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
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
class GridPaginator extends Control
{
    /** @persistent */
    public int $page = 1;
    public Paginator $paginator;
    private string $templatePath;

    public function setTemplate(string $templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function __construct()
    {
        $this->paginator = new Paginator();
    }

    public function render(): void
    {
        $this->template->paginator = $this->paginator;
        $this->template->render($this->templatePath ?? __DIR__ . '/../../templates/paginator.latte');
    }

    /**
     * @param array $params
     * @throws BadRequestException
     */
    public function loadState(array $params): void
    {
        parent::loadState($params);
        $this->paginator->page = $this->page;
    }
}
