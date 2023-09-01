<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Models\Submits\SeriesTable;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Application\UI\Template;
use Nette\DI\Container;

abstract class SeriesTableComponent extends BaseComponent
{
    protected SeriesTable $seriesTable;
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
        $template->seriesTable = $this->seriesTable;
        $template->displayAll = $this->displayAll;
        $template->lang = $this->translator->lang;
        return $template;
    }
}
