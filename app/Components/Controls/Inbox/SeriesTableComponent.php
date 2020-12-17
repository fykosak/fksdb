<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Model\Submits\SeriesTable;
use Nette\Application\UI\ITemplate;
use Nette\DI\Container;

/**
 * Class SeriesTableComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Michal Koutny
 */
abstract class SeriesTableComponent extends BaseComponent {

    private SeriesTable $seriesTable;

    private bool $displayAll;

    public function __construct(Container $context, SeriesTable $seriesTable, bool $displayAll = false) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
        $this->displayAll = $displayAll;
    }

    protected function createTemplate(): ITemplate {
        $template = parent::createTemplate();
        $template->seriesTable = $this->getSeriesTable();
        $template->displayAll = $this->displayAll;
        return $template;
    }

    protected function getSeriesTable(): SeriesTable {
        return $this->seriesTable;
    }
}
