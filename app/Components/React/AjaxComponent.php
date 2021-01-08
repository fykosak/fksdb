<?php

namespace FKSDB\Components\React;

use FKSDB\Models\React\AjaxResponse;
use Nette\Http\IRequest;
use Nette\Http\Response;

/**
 * Class AjaxComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AjaxComponent extends ReactComponent {

    private IRequest $request;

    final public function injectRequest(IRequest $request): void {
        $this->request = $request;
    }

    protected function getActions(): array {
        return [];
    }

    protected function sendAjaxResponse(int $code = Response::S200_OK): void {
        $response = new AjaxResponse();
        $response->setCode($code);
        $response->setContent($this->getResponseData());
        $this->getPresenter()->sendResponse($response);
    }

    protected function getHttpRequest(): IRequest {
        return $this->request;
    }

    protected function getResponseData(): array {
        $data = parent::getResponseData();
        $data['actions'] = json_encode($this->getActions());
        return $data;
    }
}
