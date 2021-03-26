<?php

namespace FKSDB\Components\Controls\Inbox\PointPreview;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Submits\SeriesTable;
use Nette\DI\Container;

/**
 * Class PointsPreviewControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsPreviewComponent extends SeriesTableComponent {

    public function __construct(Container $context, SeriesTable $seriesTable) {
        parent::__construct($context, $seriesTable);
    }

    final public function render(): void {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
