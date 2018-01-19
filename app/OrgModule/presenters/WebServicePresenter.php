<?php

namespace OrgModule;

use BasePresenter;
use Nette\Application\AbortException;
use Nette\Diagnostics\Debugger;
use SoapResponse;

/**
 * Description of WebServicePresenter
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class WebServicePresenter extends BasePresenter {
    /**
     * @var \SoapServer
     */
    private $server;

    public function injectSoapServer(\SoapServer $soapServer) {
        $this->server = $soapServer;
    }

    public function renderDefault() {
       
        try {
            $response = new SoapResponse($this->server);
            Debugger::barDump($response);
            Debugger::barDump($this->server);

            $this->sendResponse($response);
        } catch (AbortException $e) {
            Debugger::log($e);
            throw $e;
        } catch (\Exception $e) {
            Debugger::log($e);
            $this->redirect('Dashboard:');
        }
    }
}
