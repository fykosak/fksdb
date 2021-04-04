<?php

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ModelLogin;
use Nette\DI\Container;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class WebModel {

    use SmartObject;

    protected Container $container;
    protected ?ModelLogin $authenticatedLogin;

    final public function __construct(Container $container) {
        $this->container = $container;
        $container->callInjects($this);
    }

    final public function setLogin(?ModelLogin $authenticatedLogin): void {
        $this->authenticatedLogin = $authenticatedLogin;
    }

    abstract public function getResponse(\stdClass $args): \SoapVar;

    protected function log(string $msg): void {
        if (!isset($this->authenticatedLogin)) {
            $message = 'unauthenticated@';
        } else {
            $message = $this->authenticatedLogin->__toString() . '@';
        }
        $message .= $_SERVER['REMOTE_ADDR'] . "\t" . $msg;
        Debugger::log($message);
    }

    protected function saveXML(\DOMDocument $doc): \SoapVar {
        $nodeString = '';
        foreach ($doc->childNodes as $node) {
            $nodeString .= $doc->saveXML($node);
        }
        return new \SoapVar($nodeString, XSD_ANYXML);
    }
}
