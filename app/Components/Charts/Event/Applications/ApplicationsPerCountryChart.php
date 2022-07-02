<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\GeoCharts\GeoChart;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use Nette\Database\ResultSet;
use Nette\DI\Container;

abstract class ApplicationsPerCountryChart extends GeoChart
{

    protected ModelEvent $event;
    protected TeamMemberService $teamMemberService;

    public function __construct(Container $context, ModelEvent $event, string $scale)
    {
        parent::__construct($context, $scale);
        $this->event = $event;
    }

    public function injectSecondary(TeamMemberService $teamMemberService): void
    {
        $this->teamMemberService = $teamMemberService;
    }

    final protected function getTeams(): ResultSet
    {
        return $this->teamMemberService->explorer->query(
            'SELECT 
region.country_iso3 as `country` ,
COUNT(distinct fyziklani_team_id) as `t`, 
COUNT(*) as `p`
FROM fyziklani_team_member ep
LEFT JOIN person_history ph ON ph.person_id=ep.person_id AND ac_year = ?
LEFT JOIN school USING (school_id)
LEFT JOIN address USING (address_id)
LEFT JOIN region USING (region_id)
LEFT JOIN fyziklani_team ft USING (fyziklani_team_id)
WHERE ft.event_id = ?
GROUP BY  region.country_iso3',
            $this->event->getContestYear()->ac_year,
            $this->event->event_id
        );
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
