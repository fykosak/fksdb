<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\DI\Container;

abstract class ApplicationsPerCountryChartControl extends GeoChartControl implements IChart {

    private ModelEvent $event;
    private ServiceEventParticipant $serviceEventParticipant;
    private string $type;

    public function __construct(Container $context, ModelEvent $event, string $type, string $scale) {
        parent::__construct($context, $scale);
        $this->event = $event;
        $this->type = $type;
    }

    public function injectSecondary(ServiceEventParticipant $serviceEventParticipant): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    public function getData(): array {

        $query = $this->serviceEventParticipant->getContext()->query('SELECT 
region.country_iso3 as `country` ,
COUNT(distinct e_fyziklani_team_id) as `t`, 
COUNT(*) as `p`
FROM event 
LEFT JOIN event_participant ep USING (event_id)
LEFT JOIN event_type USING (event_type_id)
LEFT JOIN contest_year USING (contest_id, `year`)
LEFT JOIN person_history USING (person_id, ac_year)
LEFT JOIN school USING (school_id)
LEFT JOIN address USING (address_id)
LEFT JOIN region USING (region_id)
LEFT JOIN e_fyziklani_participant USING (event_participant_id)
LEFT JOIN e_fyziklani_team USING (e_fyziklani_team_id, event_id)
WHERE ep.event_id in (?)
GROUP BY  region.country_iso3', $this->event->event_id);
        $data = [];
        foreach ($query as $row) {
            $data[] = [
                'country' => $row->country,
                'count' => $this->type === 'teams' ? $row->t : ($this->type === 'participants' ? $row->p : ($row->p / $row->t)),
            ];
        }
        return $data;
    }

    public function getControl(): self {
        return $this;
    }

    public function getDescription(): ?string {
        return null;
    }
}
