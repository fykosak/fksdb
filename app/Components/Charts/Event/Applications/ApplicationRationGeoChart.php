<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class ApplicationRationGeoChart extends ApplicationsPerCountryChart
{

    public function __construct(Container $context, ModelEvent $event)
    {
        parent::__construct($context, $event, 'chart.events.application-ratio.geo');
    }

    public function getTitle(): string
    {
        return _('Participants per team');
    }

    protected function getData(): array
    {
        $data = [];
        foreach ($this->getTeams() as $row) {
            $data[$row->country] = [
                self::KEY_COUNT => ($row->p / $row->t),
            ];
        }
        return $data;
    }
}
