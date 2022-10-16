<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class ApplicationRationGeoChart extends ApplicationsPerCountryChart
{
    public function __construct(Container $context, EventModel $event)
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
