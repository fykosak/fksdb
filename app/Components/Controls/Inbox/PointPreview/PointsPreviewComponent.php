<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\PointPreview;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;

class PointsPreviewComponent extends SeriesTableComponent
{
    final public function render(): void
    {
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
