<?php

namespace FyziklaniModule;


use Nette\Application\Responses\JsonResponse;

class RegisterPresenter extends BasePresenter {


    public function renderDefault() {
        $this->template->data = [];

        if ($this->isAjax()) {


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
