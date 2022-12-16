<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use Fykosak\Utils\UI\PageTitle;

class GameSetupPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Fyziklani game setup'), 'fa fa-cogs');
    }

    /**
     * @throws EventNotFoundException
     * @throws NotFoundException
     * @throws NotSetGameParametersException
     */
    final public function renderDefault(): void
    {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @throws NotFoundException
     * @throws NotSetGameParametersException
     * @throws EventNotFoundException
     */
    protected function getGameSetup(): GameSetupModel
    {
        static $gameSetup;
        if (!isset($gameSetup) || $this->getEvent()->event_id !== $gameSetup->event_id) {
            $gameSetup = $this->getEvent()->getGameSetup();
        }
        return $gameSetup;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isAllowed('game.gameSetup', 'default'));
    }
}
