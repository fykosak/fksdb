<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Contestants;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

class ParticipantGeoChart extends FrontEndComponent implements Chart
{
    private ContestYearModel $contestYear;

    public function __construct(Container $container, ContestYearModel $contestYear)
    {
        parent::__construct($container, 'chart.contestants.geo');
        $this->contestYear = $contestYear;
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Participant per country'), 'fas fa-globe');
    }

    public function getDescription(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $data = [];
        /** @var ContestantModel $contestant */
        foreach ($this->contestYear->getContestants() as $contestant) {
            if ($contestant->getSubmits()->count() > 0) {
                $iso = $contestant->getPersonHistory()->school->address->country->alpha_3;
                if (!isset($data[$iso])) {
                    $data[$iso] = 0;
                }
                $data[$iso]++;
            }
        }
        return $data;
    }
}
