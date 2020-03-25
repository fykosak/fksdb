<?php

namespace FyziklaniModule;

use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Application\BadRequestException;

/**
 * Class GameSetupPresenter
 * @package FyziklaniModule
 */
class GameSetupPresenter extends BasePresenter {
    /**
     * @var ModelFyziklaniGameSetup
     */
    private $gameSetup;

    /**
     * @return void
     */
    public function titleDefault() {
        $this->setTitle(_('Fyziklani game setup'), 'fa fa-cogs');
    }

    /**
     * @throws BadRequestException
     */
    public function renderDefault() {
        $this->template->gameSetup = $this->getGameSetup();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        return $this->setAuthorized($this->isContestsOrgAuthorized(ModelFyziklaniGameSetup::RESOURCE_ID, 'default'));
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws BadRequestException
     */
    protected function getGameSetup(): ModelFyziklaniGameSetup {
        if (!$this->gameSetup) {
            try {
                $this->gameSetup = $this->getEvent()->getFyziklaniGameSetup();
            } catch (NotSetGameParametersException $exception) {
                throw new BadRequestException(_('Game is not set up!'), 404);
            }
        }
        return $this->gameSetup;
    }
}
