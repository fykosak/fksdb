<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Applications;

use FKSDB\Components\Charts\Core\GeoCharts\GeoChart;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use Nette\Database\ResultSet;
use Nette\DI\Container;

abstract class ApplicationsPerCountryChart extends GeoChart
{

    protected EventModel $event;
    protected TeamMemberService $teamMemberService;

    public function __construct(Container $context, EventModel $event, string $scale)
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
country.alpha_3 as `country` ,
COUNT(distinct fyziklani_team_id) as `t`, 
COUNT(*) as `p`
FROM fyziklani_team_member ep
LEFT JOIN person_history ph ON ph.person_id=ep.person_id AND ac_year = ?
LEFT JOIN school USING (school_id)
LEFT JOIN address USING (address_id)
LEFT JOIN country USING (country_id)
LEFT JOIN fyziklani_team ft USING (fyziklani_team_id)
WHERE ft.event_id = ?
GROUP BY  country.alpha_3',
            $this->event->getContestYear()->ac_year,
            $this->event->event_id
        );
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
