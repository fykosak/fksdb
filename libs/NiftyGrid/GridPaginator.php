<?php

declare(strict_types=1);

/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author    Jakub Holub
 * @author      Michal Koutny (modified template path from 4a2fdc6c03)
 * @copyright    Copyright (c) 2012 Jakub Holub
 * @license    New BSD Licence
 * @link    http://addons.nette.org/cs/niftygrid
 */

namespace NiftyGrid;

use Nette\Utils\Paginator;

class GridPaginator extends \Nette\Application\UI\Control {

    /** @persistent int */
    public $page = 1;

    /**
     * @var Paginator
     */
    public $paginator;

    /**
     * @var str
     */
    private $templatePath;

    public function setTemplate($templatePath) {
        $this->templatePath = $templatePath;
    }

    public function __construct() {
        $this->paginator = new Paginator();
    }

    public function render(): void {
        $this->template->paginator = $this->paginator;
        $templatePath = $this->templatePath ?? __DIR__ . "/../../templates/paginator.latte";
        $this->template->setFile($templatePath);
        $this->template->render();
    }

    public function loadState(array $params): void {
        parent::loadState($params);
        $this->paginator->page = $this->page;
    }

}
