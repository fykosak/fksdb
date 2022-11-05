<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

class RestApiPresenter extends AuthenticatedPresenter
{

    private WebServiceModel $server;
    /** @persistent */
    public string $webServiceName;

    final public function injectSoapServer(WebServiceModel $server, ContestAuthorizator $contestAuthorizator): void
    {
        $this->server = $server;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /* TODO */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('webService', 'default'));
    }

    /**
     * @throws BadRequestException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    final protected function beforeRender(): void
    {
        try {
            $response = $this->server->getJsonResponse($this->webServiceName, $this->getParameters());
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            throw $exception;
        }
    }

    public function getAllowedAuthMethods(): array
    {
        return [
            self::AUTH_HTTP => true,
            self::AUTH_LOGIN => true,
            self::AUTH_TOKEN => false,
        ];
    }

    protected function getHttpRealm(): ?string
    {
        return 'JSON';
    }
}
