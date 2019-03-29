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
     * @throws AbortException
     */
    public function renderDefault() {
        $server = $this->getService('soapServer');
        try {
            $response = new SoapResponse($server);
            $this->sendResponse($response);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->redirect('Dashboard:');
        }
    }

}
