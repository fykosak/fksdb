<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\PDFGenerators\PageComponent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;

abstract class AbstractPageComponent extends BaseComponent implements PageComponent
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
}
