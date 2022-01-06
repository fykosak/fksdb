<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\WebService\SoapResponse;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;

class WebServicePresenter extends BasePresenter
{

    private \SoapServer $server;
    private ContestAuthorizator $contestAuthorizator;

    final public function injectSoapServer(\SoapServer $server, ContestAuthorizator $contestAuthorizator): void
    {
        $this->server = $server;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /* TODO */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->contestAuthorizator->isAllowed('webService', 'default'));
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
