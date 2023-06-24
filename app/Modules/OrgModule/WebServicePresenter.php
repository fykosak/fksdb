<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\WebService\SoapResponse;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;

class WebServicePresenter extends BasePresenter
{
    private \SoapServer $server;

    final public function injectSoapServer(\SoapServer $server): void
    {
        $this->server = $server;
    }

    public function authorizedDefault(): bool
    {
        return $this->contestAuthorizator->isAllowed('webService', 'default');
    }

    final public function renderDefault(): void
    {
        try {
            $response = new SoapResponse($this->server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->redirect('Dashboard:');
        }
    }
}
