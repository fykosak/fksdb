<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
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
    protected function getGameSetup(): ModelFyziklaniGameSetup
    {
        static $gameSetup;
        if (!isset($gameSetup)) {
            $gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            if (!$gameSetup) {
                throw new NotFoundException(_('Game is not set up!'));
            }
        }
        return $gameSetup;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isAllowed('fyziklani.gameSetup', 'default'));
    }
}
