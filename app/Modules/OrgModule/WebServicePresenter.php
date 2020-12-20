<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;
use FKSDB\Models\WebService\SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {

    private \SoapServer $server;

    final public function injectSoapServer(\SoapServer $server): void {
        $this->server = $server;
    }

    /**
     * @throws AbortException
     */
    public function renderDefault(): void {
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
