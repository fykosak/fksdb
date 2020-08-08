<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use Tracy\Debugger;
use FKSDB\WebService\SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {
    /** @var \SoapServer */
    private $server;

    /**
     * @param \SoapServer $server
     * @return void
     */
    public function injectSoapServer(\SoapServer $server) {
        $this->server = $server;
    }

    /**
     * @throws AbortException
     */
    public function renderDefault() {
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
