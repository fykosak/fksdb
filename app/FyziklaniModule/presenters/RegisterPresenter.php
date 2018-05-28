<?php

namespace FyziklaniModule;


use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Application\Responses\JsonResponse;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\FireLogger;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class RegisterPresenter extends BasePresenter {
    /**
     * @var SchoolProvider
     */
    private $schoolProvider;

    public function injectSchoolProvider(SchoolProvider $schoolProvider) {
        $this->schoolProvider = $schoolProvider;
    }

    /**
     * @var \ServicePerson
     */
    private $servicePerson;

    public function injectServicePerson(\ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleSchoolProvider(\ReactResponse $response) {
        $data = $this->schoolProvider->getFilteredItems($this->getHttpRequest()->getPost('payload'));
        FireLogger::log($data);
        $response->setData($data);
        $response->setAct('school-provider');
        $this->sendResponse($response);
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handlePersonProvider(\ReactResponse $response) {
        $email = $this->getHttpRequest()->getPost('email');
        $person = $this->servicePerson->findByEmail($email);
        $data = $this->getParticipantData($person, $email);
        $response->setData($data);
        $response->setAct('person-provider');
        $this->sendResponse($response);
    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleTeamNameUnique(\ReactResponse $response) {
        $name = $this->getHttpRequest()->getPost('name');
        $count = $this->serviceFyziklaniTeam->getTable()->where('name=?', $name)->where('event_id', $this->getEventId())->count();

        $data = ['result' => true];
        if ($count) {
            $data['result'] = false;
            $response->addMessage(new \ReactMessage(_('Meno je už použité'), 'danger'));
        }
        $response->setData($data);
        $response->setAct('team-name-unique');
        $this->sendResponse($response);

    }

    /**
     * @param \ReactResponse $response
     * @throws \Nette\Application\AbortException
     */
    private function handleLangDownload(\ReactResponse $response) {
        $keys = ['Other name', 'Team name', 'Family name',
            'E-mail', 'School',
            'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť',
            'Opraviť hodnotu',
            'Study year',
            'Doprovodný program o ktorý mám zaujem.',
            'E-mail',
            'hledat',

        ];
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = _($key);
        }
        $response->setAct('lang-downloader');
        $response->setData($data);
        $this->sendResponse($response);
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws JsonException
     */
    public function renderDefault() {

        if ($this->isAjax()) {
            FireLogger::log($this->getHttpRequest());
            $response = new \ReactResponse();
            switch ($this->getHttpRequest()->getPost('act')) {
                case 'school-provider':
                    $this->handleSchoolProvider($response);
                    break;
                case 'person-provider' :
                    $this->handlePersonProvider($response);
                    break;
                case 'team-name-unique':
                    $this->handleTeamNameUnique($response);
                    break;
                case 'lang-downloader':
                    sleep(10);
                    $this->handleLangDownload($response);
                    break;
            }

        } else {
            $this->template->data = [];
            $this->template->accDef = '[{"accId":1,"date":"2017-05-02","name":"Elf","price":{"eur":10,"kc":300}},{"accId":2,"date":"2017-05-03","name":"Elf","price":{"eur":10,"kc":300}},{"accId":3,"date":"2017-05-04","name":"Elf","price":{"eur":10,"kc":300}},{"accId":4,"date":"2017-05-05","name":"Elf","price":{"eur":10,"kc":300}},{"accId":5,"date":"2017-05-03","name":"Duo","price":{"eur":20,"kc":500}},{"accId":6,"date":"2017-05-04","name":"Duo","price":{"eur":20,"kc":500}},{"accId":7,"date":"2017-05-05","name":"Duo","price":{"eur":20,"kc":500}}]';

            $this->template->scheduleDef = Json::encode($this->getEvent()->getParameter('schedule'));
            $this->template->personsDef = '[{"fields":[],"index":0,"type":"participant"},{"fields":[],"index":1,"type":"participant"},{"fields":[],"index":2,"type":"participant"},{"fields":[],"index":3,"type":"participant"},{"fields":[],"index":4,"type":"participant"},{"fields":[],"index":0,"type":"teacher"}]';
            $this->template->studyYearsDef = Json::encode($this->getStudyYears());
        }

    }

    private function getStudyYears() {

        $hsYears = [];
        foreach (range(1, 4) as $study_year) {
            $hsYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, 2017));
        }

        $primaryYears = [];
        foreach (range(6, 9) as $study_year) {
            $primaryYears[$study_year] = sprintf(_('%d. ročník (očekávaný rok maturity %d)'), $study_year, $this->yearCalculator->getGraduationYear($study_year, 2017));
        }

        return [
            _('střední škola') => $hsYears,
            _('základní škola nebo víceleté gymnázium') => $primaryYears,
        ];

    }

    /**
     * @param $person \ModelPerson
     * @param $email string
     * @return array
     */
    private
    function getParticipantData($person, $email) {
        $data = ['fields' =>
            [
                'email' => ['value' => $email, 'hasValue' => true,],
            ],
        ];
        if (!$person) {
            $data['fields']['personId'] = ['value' => null, 'hasValue' => true,];
            $data['fields']['school'] = ['value' => [], 'hasValue' => false,];
            $data['fields']['studyYear'] = ['value' => null, 'hasValue' => false,];
            $data['fields']['idNumber'] = ['value' => null, 'hasValue' => false,];
            $data['fields']['familyName'] = ['value' => null, 'hasValue' => false,];
            $data['fields']['otherName'] = ['value' => null, 'hasValue' => false,];
        } else {
            $data['fields']['personId'] = ['value' => $person->person_id, 'hasValue' => true,];
            $data['fields']['school'] = ['value' => ['label' => 'G Púchov', 'id' => 5376], 'hasValue' => true,];
            $data['fields']['studyYear'] = ['value' => '', 'hasValue' => true,];
            $data['fields']['idNumber'] = ['value' => null, 'hasValue' => $person->getInfo()->id_number ? true : false,];
            $data['fields']['familyName'] = ['value' => $person->family_name, 'hasValue' => true,];
            $data['fields']['otherName'] = ['value' => $person->other_name, 'hasValue' => true,];
        }
        return $data;
    }
}
