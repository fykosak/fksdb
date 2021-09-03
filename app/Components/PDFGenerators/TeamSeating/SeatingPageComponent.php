<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;

abstract class SeatingPageComponent extends AbstractPageComponent
{

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    final public function injectServicePrimary(
        ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
    ): void {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @param mixed $row
     */
    public function render($row): void
    {
        $this->template->places = $this->serviceFyziklaniTeamPosition->getAllPlaces($this->getRooms());
    }

    private function getRooms(): array
    {
        return [10, 11, 12, 13, 14];
    }

    public function getPagesTemplatePath(): string
    {
        return $this->formatPathByFormat(self::FORMAT_A5_PORTRAIT);
    }
}
