<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Submits\SeriesTable;
use Nette\DI\Container;
use Nette\Templating\ITemplate;

/**
 * Class SeriesTableComponent
 * @author Michal Červeňák <miso@fykos.cz>
 * @author Michal Koutny
 */
abstract class SeriesTableComponent extends BaseComponent {
    /** @var SeriesTable */
    private $seriesTable;
    /** @var bool */
    private $displayAll;

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     * @param bool $displayAll
     */
    public function __construct(Container $context, SeriesTable $seriesTable, bool $displayAll = false) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
        $this->displayAll = $displayAll;
    }

    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->seriesTable = $this->getSeriesTable();
        $template->displayAll = $this->displayAll;
        return $template;
    }

    protected function getSeriesTable(): SeriesTable {
        return $this->seriesTable;
    }
}
