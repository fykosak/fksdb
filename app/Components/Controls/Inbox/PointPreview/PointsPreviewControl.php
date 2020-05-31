<?php

namespace FKSDB\Components\Controls\Inbox;

/**
 * Class PointsPreviewControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PointsPreviewControl extends SeriesTableComponent {

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->template->render();
    }
}
