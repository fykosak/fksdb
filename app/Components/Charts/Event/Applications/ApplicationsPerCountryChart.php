<?php

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\GeoCharts\GeoChart;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Nette\Database\ResultSet;
use Nette\DI\Container;

abstract class ApplicationsPerCountryChart extends GeoChart
{

    protected ModelEvent $event;
    protected ServiceEventParticipant $serviceEventParticipant;

    public function __construct(Container $context, ModelEvent $event, string $scale)
    {
        parent::__construct($context, $scale);
        $this->event = $event;
    }

    public function injectSecondary(ServiceEventParticipant $serviceEventParticipant): void
    {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    final protected function getTeams(): ResultSet
    {
        return $this->serviceEventParticipant->explorer->query('SELECT 
region.country_iso3 as `country` ,
COUNT(distinct e_fyziklani_team_id) as `t`, 
COUNT(*) as `p`
FROM event_participant ep
LEFT JOIN person_history ph ON ph.person_id=ep.person_id AND ac_year = ?
LEFT JOIN school USING (school_id)
LEFT JOIN address USING (address_id)
LEFT JOIN region USING (region_id)
LEFT JOIN e_fyziklani_participant USING (event_participant_id)
LEFT JOIN e_fyziklani_team USING (e_fyziklani_team_id, event_id)
WHERE ep.event_id in (?)
GROUP BY  region.country_iso3', $this->event->getContestYear()->ac_year, $this->event->event_id);
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
