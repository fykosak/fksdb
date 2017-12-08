<?php

namespace FyziklaniModule;

use Astrid\Downloader;
use Astrid\DownloadException;
use BrawlLib\Components\Routing;
use BrawlLib\Components\RoutingDownload;
use BrawlLib\Components\RoutingEdit;
use FKS\Application\UploadException;
use FKSDB\model\Fyziklani\Rooms\PipelineFactory;

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Logging\FlashDumpFactory;
use ModelException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;
use ORM\Models\Events\ModelFyziklaniTeam;
use Pipeline\PipelineException;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsPresenter extends BasePresenter {
    /**
     * @var \ServiceBrawlRoom
     */
    private $serviceBrawlRoom;
    /**
     * @var \ServiceBrawlTeamPosition
     */
    protected $serviceBrawlTeamPosition;


    public function injectServiceBrawlRoom(\ServiceBrawlRoom $serviceBrawlRoom) {
        $this->serviceBrawlRoom = $serviceBrawlRoom;
    }

    public function injectServiceBrawlTeamPosition(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition) {
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    public function titleDefault() {
        $this->setTitle(_('Rozdělení do místností'));
    }

    public function titleEdit() {
        $this->setTitle(_('Edit routing'));
    }

    public function titleDownload() {
        $this->setTitle(_('Download routing'));
    }

    public function authorizedEdit() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'roomsImport')));
    }

    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'roomsDownload')));
    }

    public function authorizedDefault() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function renderEdit() {
        if ($this->isAjax()) {
            $data = Json::decode($this->getHttpRequest()->getPost('data'));
            $updatedTeams = $this->serviceBrawlTeamPosition->updateRouting($data);
            $this->sendResponse(new JsonResponse(['updatedTeams' => $updatedTeams]));
        }
    }

    private function getRooms() {
        return $this->serviceBrawlRoom->getRoomsById($this->getCurrentEvent()->getParameter('rooms'));
    }

    public function createComponentDownload() {
        $control = new RoutingDownload($this->getTranslator());
        $control->setBuildings(['M', 'F', 'S']);
        $control->setRooms($this->getRooms());
        $control->setTeams($this->serviceFyziklaniTeam->getTeams($this->eventID));
        return $control;
    }


    public function createComponentRouting() {
        $control = new RoutingEdit();
        $data = [
            'teams' => $this->serviceFyziklaniTeam->getTeams($this->eventID),
            'rooms' => $this->getRooms(),
        ];
        $control->setData($data);
        return $control;
    }
}
