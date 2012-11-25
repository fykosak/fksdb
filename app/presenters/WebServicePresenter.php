<?php

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
        } catch (\Nette\Application\AbortException $e) {
            throw $e;
        } catch (Exception $e) {
            \Nette\Diagnostics\Debugger::log($e);
            $this->redirect('Dashboard:');
        }
    }

}