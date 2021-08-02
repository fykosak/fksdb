<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Submits\SeriesTable;
use Nette\Application\UI\Template;
use Nette\DI\Container;

abstract class SeriesTableComponent extends BaseComponent
{

    private SeriesTable $seriesTable;

    private bool $displayAll;

    public function __construct(Container $context, SeriesTable $seriesTable, bool $displayAll = false)
    {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
        $this->displayAll = $displayAll;
    }

    protected function createTemplate(): Template
    {
        $template = parent::createTemplate();
        $template->seriesTable = $this->getSeriesTable();
        $template->displayAll = $this->displayAll;
        return $template;
    }

    protected function getSeriesTable(): SeriesTable
    {
        return $this->seriesTable;
    }
}
