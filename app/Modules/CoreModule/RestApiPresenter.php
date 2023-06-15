<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\WebService\WebServiceModel;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\AuthMethod;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

class RestApiPresenter extends AuthenticatedPresenter
{

    private WebServiceModel $server;
    /** @persistent */
    public string $webServiceName;

    final public function injectSoapServer(WebServiceModel $server): void
    {
        $this->server = $server;
    }

    /* TODO */
    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed('webService', 'default');
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

    public function isAuthAllowed(AuthMethod $authMethod): bool
    {
        switch ($authMethod->value) {
            case AuthMethod::LOGIN:
            case AuthMethod::HTTP:
                return true;
            case AuthMethod::TOKEN:
                return false;
        }
        return false;
    }

    protected function getHttpRealm(): ?string
    {
        return 'JSON';
    }
}
