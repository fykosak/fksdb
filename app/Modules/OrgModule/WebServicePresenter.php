<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use SoapServer;
use Tracy\Debugger;
use FKSDB\Models\WebService\SoapResponse;

class WebServicePresenter extends BasePresenter {

    private SoapServer $server;

    final public function injectSoapServer(SoapServer $server): void {
        $this->server = $server;
    }

    /**
     * @throws AbortException
     */
    final public function renderDefault(): void {
        try {
            $response = new SoapResponse($this->server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->redirect('Dashboard:');
        }
    }
}
