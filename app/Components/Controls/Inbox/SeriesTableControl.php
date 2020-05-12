<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\Submits\SeriesTable;
use Nette\DI\Container;
use Nette\Application\UI\ITemplate;

/**
 * Class SeriesTableControl
 * @package FKSDB\Components\Controls\Upload
 */
abstract class SeriesTableControl extends BaseControl {
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

    /**
     * @return SeriesTable
     */
    protected function getSeriesTable(): SeriesTable {
        return $this->seriesTable;
    }
}
