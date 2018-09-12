<?php

trait ReactRequest {
    /**
     * @return object
     */
    protected function getReactRequest() {
        $requestData = $this->getHttpRequest()->getPost('requestData');
        $act = $this->getHttpRequest()->getPost('act');
        return (object)['requestData' => $requestData, 'act' => $act];
    }
}
