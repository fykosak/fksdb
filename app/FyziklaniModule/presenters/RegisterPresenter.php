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
                    'school' => ['value' => '', 'hasValue' => true,],
                    'studyYear' => ['value' => '', 'hasValue' => true,],
                    'idNumber' => ['value' => '', 'hasValue' => false,],
                    'familyName' => ['value' => 'Červeňák', 'hasValue' => true,],
                    'otherName' => ['value' => 'Michal', 'hasValue' => true,],
                ]
            ];
            $this->sendResponse(new JsonResponse($data));

        }

    }
}
