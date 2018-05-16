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
     * @throws \Nette\Application\AbortException
     */
    public function renderDefault() {
        $this->template->data = [];
        $this->template->accDef = '[{"accId":1,"date":"2017-05-02","name":"Elf","price":{"eur":10,"kc":300}},{"accId":2,"date":"2017-05-03","name":"Elf","price":{"eur":10,"kc":300}},{"accId":3,"date":"2017-05-04","name":"Elf","price":{"eur":10,"kc":300}},{"accId":4,"date":"2017-05-05","name":"Elf","price":{"eur":10,"kc":300}},{"accId":5,"date":"2017-05-03","name":"Duo","price":{"eur":20,"kc":500}},{"accId":6,"date":"2017-05-04","name":"Duo","price":{"eur":20,"kc":500}},{"accId":7,"date":"2017-05-05","name":"Duo","price":{"eur":20,"kc":500}}]';

        if ($this->isAjax()) {
            FireLogger::log($this->getHttpRequest());
            if ($this->getHttpRequest()->getPost('act') == 'school-provider') {
                $data = $this->schoolProvider->getFilteredItems($this->getHttpRequest()->getPost('payload'));
                FireLogger::log($data);
                $this->sendResponse(new JsonResponse($data));
                die();
            }


            $data = [
                'fields' => [
                    'personId' => ['value' => 324, 'hasValue' => true,],
                    'email' => ['value' => 'miso@fykos.cz', 'hasValue' => true,],
                    'school' => ['value' => ['label' => 'G Púchov', 'id' => 5376], 'hasValue' => true,],
                    'studyYear' => ['value' => '', 'hasValue' => true,],
                    'idNumber' => ['value' => '', 'hasValue' => true,],
                    'familyName' => ['value' => 'Červeňák', 'hasValue' => true,],
                    'otherName' => ['value' => 'Michal', 'hasValue' => true,],
                ]
            ];
            $this->sendResponse(new JsonResponse($data));

        }

    }
}
