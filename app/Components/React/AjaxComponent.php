<?php

namespace FKSDB\Components\React;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\React\AjaxResponse;
use Nette\Application\AbortException;
use Nette\Http\IRequest;
use Nette\Http\Response;

/**
 * Class AjaxComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AjaxComponent extends ReactComponent2 {

    protected function getActions(): array {
        return [];
    }

    /**
     * @param int $code
     * @return void
     * @throws AbortException
     */
    protected function sendAjaxResponse(int $code = Response::S200_OK): void {
        $response = new AjaxResponse();
        $response->setCode($code);
        $response->setContent($this->getResponseData());
        $this->getPresenter()->sendResponse($response);
    }

    /**
     * @return IRequest
     * @throws BadTypeException
     */
    protected function getHttpRequest(): IRequest {
        $service = $this->getContext()->getByType(IRequest::class);
        if ($service instanceof IRequest) {
            return $service;
        }
        throw new BadTypeException(IRequest::class, $service);
    }

    protected function getResponseData(): array {
        $data = parent::getResponseData();
        $data['actions'] = json_encode($this->getActions());
        return $data;
    }
}
