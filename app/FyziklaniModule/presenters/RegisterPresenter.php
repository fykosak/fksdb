<?php

namespace FyziklaniModule;


use FKSDB\Components\Forms\Controls\Autocomplete\SchoolProvider;
use Nette\Application\Responses\JsonResponse;
use Nette\Diagnostics\FireLogger;

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
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault() {
        $this->template->data = [];
        $this->template->accDef = '[{"accId":1,"date":"2017-05-02","name":"Elf","price":{"eur":10,"kc":300}},{"accId":2,"date":"2017-05-03","name":"Elf","price":{"eur":10,"kc":300}},{"accId":3,"date":"2017-05-04","name":"Elf","price":{"eur":10,"kc":300}},{"accId":4,"date":"2017-05-05","name":"Elf","price":{"eur":10,"kc":300}},{"accId":5,"date":"2017-05-03","name":"Duo","price":{"eur":20,"kc":500}},{"accId":6,"date":"2017-05-04","name":"Duo","price":{"eur":20,"kc":500}},{"accId":7,"date":"2017-05-05","name":"Duo","price":{"eur":20,"kc":500}}]';
        $this->template->scheduleDef = '[{"date":"2017-05-02","description":"<a href=\"http://matfyz.cz\">viac info</a> jeden den s fyzikou","id":1,"scheduleName":"JDF","time":{"begin":"12:00","end":"15:00"}},{"date":"2017-04-02","description":"DSEF","id":2,"price":{"eur":10,"kc":300},"scheduleName":"DSEF","time":{"begin":"12:00","end":"15:00"}},{"date":"2017-06-02","description":"Afterparty","id":3,"scheduleName":"Afterparty","time":{"begin":"12:00","end":"15:00"}}]';
        if ($this->isAjax()) {
            FireLogger::log($this->getHttpRequest());
            if ($this->getHttpRequest()->getPost('act') == 'school-provider') {
                $data = $this->schoolProvider->getFilteredItems($this->getHttpRequest()->getPost('payload'));
                FireLogger::log($data);
                $this->sendResponse(new JsonResponse($data));
            } elseif ($this->getHttpRequest()->getPost('act') == 'person-provider') {
                $email = $this->getHttpRequest()->getPost('email');
                $person = $this->servicePerson->findByEmail($email);
                $data = $this->getParticipantData($person, $email);
                $this->sendResponse(new JsonResponse($data));
            }
        }

    }

    /**
     * @param $person \ModelPerson
     * @param $email string
     * @return array
     */
    private function getParticipantData( $person, $email) {
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
