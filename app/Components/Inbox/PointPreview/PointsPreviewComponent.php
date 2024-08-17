<?php

declare(strict_types=1);

namespace FKSDB\Components\Inbox\PointPreview;

use FKSDB\Components\Inbox\SeriesTableComponent;

class PointsPreviewComponent extends SeriesTableComponent
{
    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
