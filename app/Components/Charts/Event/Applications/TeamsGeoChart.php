<?php

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class TeamsGeoChart extends ApplicationsPerCountryChart
{

    public function __construct(Container $context, ModelEvent $event)
    {
        parent::__construct($context, $event, 'chart.events.teams.geo');
    }

    public function getTitle(): string
    {
        return _('Teams per country');
    }

    protected function getData(): array
    {
        $data = [];
        foreach ($this->getTeams() as $row) {
            $data[$row->country] = [
                self::KEY_COUNT => $row->t,
            ];
        }
        return $data;
    }
}
