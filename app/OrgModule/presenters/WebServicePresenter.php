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

    public function renderDefault() {
        $server = $this->getService('soapServer');
        try {
            $response = new SoapResponse($server);
            $this->sendResponse($response);
        } catch (AbortException $e) {
            Debugger::log($e);
            throw $e;
        } catch (Exception $e) {
            Debugger::log($e);
            $this->redirect('Dashboard:');
        }
    }

}
