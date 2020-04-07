<?php

namespace FKSDB\Components\Controls\Upload;

use FKSDB\Components\Controls\BaseControl;
use FKSDB\Submits\SeriesTable;
use Nette\DI\Container;
use Nette\Templating\ITemplate;

/**
 * Class SeriesTableControl
 * @package FKSDB\Components\Controls\Upload
 */
abstract class SeriesTableControl extends BaseControl {
    /**
     * @var SeriesTable
     */
    private $seriesTable;

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context);
        $this->seriesTable = $seriesTable;
    }

    /**
     * @param null $class
     * @return ITemplate
     */
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->seriesTable = $this->getSeriesTable();
        return $template;
    }

    /**
     * @return SeriesTable
     */
    protected function getSeriesTable(): SeriesTable {
        return $this->seriesTable;
    }
}
