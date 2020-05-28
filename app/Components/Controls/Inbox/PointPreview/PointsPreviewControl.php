<?php

namespace FKSDB\Components\Controls\Inbox;

use FKSDB\Submits\SeriesTable;
use Nette\DI\Container;

/**
 * Class PointsPreviewControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsPreviewControl extends SeriesTableComponent {

    /**
     * CheckSubmitsControl constructor.
     * @param Container $context
     * @param SeriesTable $seriesTable
     */
    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context, $seriesTable);
    }

    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR.'layout.latte');
        $this->template->render();
    }
}
