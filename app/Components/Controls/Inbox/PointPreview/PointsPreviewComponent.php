<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Inbox\PointPreview;

use FKSDB\Components\Controls\Inbox\SeriesTableComponent;
use FKSDB\Models\Submits\SeriesTable;
use Nette\DI\Container;

class PointsPreviewComponent extends SeriesTableComponent
{

    public function __construct(Container $context, SeriesTable $seriesTable)
    {
        parent::__construct($context, $seriesTable);
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
    }
}
