<?php

namespace FyziklaniModule;

use Astrid\Downloader;
use Astrid\DownloadException;
use BrawlLib\Components\Routing;
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

    const SOURCE_ASTRID = 'astrid';

    const SOURCE_FILE = 'file';

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var FlashDumpFactory
     */
    private $flashDumpFactory;

    /**
     * @var \ServiceBrawlRoom
     */
    private $serviceBrawlRoom;
    /**
     * @var \ServiceBrawlTeamPosition
     */
    protected $serviceBrawlTeamPosition;

    public function injectDownloader(Downloader $downloader) {
        $this->downloader = $downloader;
    }

    public function injectServiceBrawlRoom(\ServiceBrawlRoom $serviceBrawlRoom) {
        $this->serviceBrawlRoom = $serviceBrawlRoom;
    }

    public function injectServiceBrawlTeamPosition(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition) {
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    public function injectPipelineFactory(PipelineFactory $pipelineFactory) {
        $this->pipelineFactory = $pipelineFactory;
    }

    function injectFlashDumpFactory(FlashDumpFactory $flashDumpFactory) {
        $this->flashDumpFactory = $flashDumpFactory;
    }

    public function titleImport() {
        $this->setTitle(_('Import rozdělení do místností'));
    }

    public function titleEdit() {
        $this->setTitle(_('Edit routing'));
    }

    public function titleDownload() {
        $this->setTitle(_('Download routing'));
    }

    public function authorizedImport() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'roomsImport')));
    }

    protected function createComponentRoomsImportForm() {
        $seriesForm = new Form();
        $seriesForm->setRenderer(new BootstrapRenderer());

        $source = $seriesForm->addRadioList('source', _('Zdroj dat'), array(
            self::SOURCE_ASTRID => _('Astrid'),
            self::SOURCE_FILE => _('CSV soubor'),
        ));
        $source->setDefaultValue(self::SOURCE_ASTRID);

        // Astrid download -- has no special form elements
        // File upload
        $upload = $seriesForm->addUpload('file', _('CSV soubor rozdělení'));
        $upload->addConditionOn($source, Form::EQUAL, self::SOURCE_FILE)->toggle($upload->getHtmlId() . '-pair');

        $seriesForm->addSubmit('submit', _('Importovat'));

        $seriesForm->onSuccess[] = callback($this, 'validRoomsImportForm');

        return $seriesForm;
    }

    /**
     * @param $data
     * @return string[]
     */
    protected function saveRouting($data) {
        $updatedTeams = [];
        foreach ($data as $teamData) {
            try {
                /**
                 * @var $model \ModelBrawlTeamPosition
                 */
                $model = $this->serviceBrawlTeamPosition->findByTeamId($teamData->teamID);
                if (!is_null($teamData->x) && !is_null($teamData->y)) {
                    /**
                     * @var $room \ModelBrawlRoom
                     */
                    $room = $this->serviceBrawlRoom->findByName($teamData->room);

                    $data = [
                        'e_fyziklani_team_id' => $teamData->teamID,
                        'row' => $teamData->y,
                        'col' => $teamData->x,
                        'room_id' => $room->room_id,
                    ];
                    if (!$model) {
                        $model = $this->serviceBrawlTeamPosition->createNew($data);
                    } else {
                        $this->serviceBrawlTeamPosition->updateModel($model, $data);
                    }
                    $this->serviceBrawlTeamPosition->save($model);
                    $updatedTeams[] = $teamData->teamID;
                } else {
                    if ($model) {
                        $model->delete();
                        $updatedTeams[] = $teamData->teamID;
                    }
                }
            } catch (\Exception $e) {
            }

        }
        return $updatedTeams;
    }

    public function renderEdit() {
        if ($this->isAjax()) {
            $data = Json::decode($this->getHttpRequest()->getPost('data'));
            $updatedTeams = $this->saveRouting($data);
            $this->sendResponse(new JsonResponse(['updatedTeams' => $updatedTeams]));
        }
    }

    public function renderDownload() {
        $this->template->rooms = $this->getRooms();
        /*     $teams = [];
             foreach ($this->getTeams() as $team) {
                 $team['color'] = '#' . substr(md5($team['name']), 0, 6);

                 $teams[] = $team;
             }

             $this->template->teams = $teams;*/
        $this->template->dataRooms = json_encode($this->getRooms());
        $this->template->dataTeams = json_encode($this->getTeams());
    }

    private function getRooms() {
        $roomIds = $this->getCurrentEvent()->getParameter('rooms');
        $rooms = [];
        foreach ($roomIds as $roomId) {
            /**
             * @var $room \ModelBrawlRoom
             */
            $room = $this->serviceBrawlRoom->findByPrimary($roomId);
            if ($room) {
                $rooms[] = [
                    'roomId' => $room->room_id,
                    'name' => $room->name,
                    'x' => $room->columns,
                    'y' => $room->rows,
                ];
            }
        }
        return $rooms;
    }

    private function getTeams() {
        // TODO vytiahnuť školy/učastnikov
        /**
         * @var $team ModelFyziklaniTeam
         * @var $position \ModelBrawlTeamPosition
         * @var $room \ModelBrawlRoom
         */
        $teams = [];
        foreach ($this->serviceFyziklaniTeam->findParticipating($this->eventID) as $team) {

            $position = $this->serviceBrawlTeamPosition->getTable()->where('e_fyziklani_team_id', $team->e_fyziklani_team_id)->fetch();
            $roomName = null;
            if ($position) {
                $room = $this->serviceBrawlRoom->findByPrimary($position->room_id);
                $roomName = $room->name;
            }

            $teams[] = [
                'teamID' => $team->e_fyziklani_team_id,
                'name' => $team->name,
                'category' => $team->category,
                'room' => $roomName,
                'x' => $position ? $position->col : null,
                'y' => $position ? $position->row : null,
            ];
        };
        return $teams;
    }

    public function createComponentRouting() {
        $control = new Routing();
        $data = [
            'teams' => $this->getTeams(),
            'rooms' => $this->getRooms(),
        ];
        $control->setData($data);
        return $control;
    }

    public function validRoomsImportForm(Form $seriesForm) {
        $values = $seriesForm->getValues();

        try {
            // obtain file
            switch ($values['source']) {
                case self::SOURCE_ASTRID:
                    $contest = $this->getCurrentEvent()->getContest();
                    $year = $this->getCurrentEvent()->year;
                    $file = $this->downloader->downloadFyziklaniRooms($contest, $year);
                    break;
                case self::SOURCE_FILE:
                    if (!$values['file']->isOk()) {
                        throw new UploadException();
                    }
                    $file = $values['file']->getTemporaryFile();
                    break;
            }

            // process file
            $pipeline = $this->pipelineFactory->create($this->getCurrentEvent());

            $pipeline->setInput($file);
            $pipeline->run();
            unlink($file);

            $dump = $this->flashDumpFactory->createDefault();
            $dump->dump($pipeline->getLogger(), $this);
            $this->flashMessage(_('Rozdělení týmů importováno.'), self::FLASH_SUCCESS);
        } catch (DownloadException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo stáhnout.'), self::FLASH_ERROR);
        } catch (UploadException $e) {
            $this->flashMessage(_('Soubor s rozdělením týmů se nepodařilo uploadovat.'), self::FLASH_ERROR);
        } catch (PipelineException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo uložit.'), self::FLASH_ERROR);
            Debugger::log($e);
        } catch (ModelException $e) {
            $this->flashMessage(_('Rozdělení týmů se nepodařilo uložit.'), self::FLASH_ERROR);
            Debugger::log($e);
        }

        $this->redirect('this');
    }

}
