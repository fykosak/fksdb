<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;

class RestApiPresenter extends AuthenticatedPresenter
{

    private WebServiceModel $server;

    final public function injectSoapServer(WebServiceModel $server, ContestAuthorizator $contestAuthorizator): void
    {
        $this->server = $server;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /* TODO */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowedForAnyContest('webService', 'default'));
    }

    final protected function beforeRender(): void
    {
        try {
            $response = $this->server->getJsonResponse($this->getView(), $this->getParameters());
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Debugger::barDump($exception);
            throw $exception;
        }
    }
}
