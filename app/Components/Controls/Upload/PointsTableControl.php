<?php

namespace FKSDB\Components\Controls\Upload;

use FKSDB\Submits\SeriesTable;
use Nette\DI\Container;

/**
 * Class PointsTableControl
 * @package FKSDB\Components\Controls\Upload
 */
class PointsTableControl extends SeriesTableControl {

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context, $seriesTable);
    }

    /**
     * @inheritDoc
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PointsTableControl.latte');
        $this->template->render();
    }
}
