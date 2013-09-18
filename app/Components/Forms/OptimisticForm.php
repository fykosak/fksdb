<?php

namespace FKSDB\Components\Forms;

use LogicException;
use Nette\Application\UI\Form;
use Nette\Callback;

/**
 * Form that uses optimistic locking to control multiple user access.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class OptimisticForm extends Form {

    const FINGERPRINT = '__fp';

    /**
     * @var Callback
     */
    private $fingerprintCallback;

    /**
     * @var Callback
     */
    private $defaultsCallback;

    /**
     * @var string
     */
    private $customError;

    /**
     * @var boolean
     */
    private $refreshOnConflict = true;

    /**
     * 
     * @param callback $fingerprintCallback returns fingerprint of current version of the data
     * @param callback $defaultsCallback    returns current version of data, formatted as an array
     */
    public function __construct($fingerprintCallback, $defaultsCallback) {
        parent::__construct();

        $this->fingerprintCallback = new Callback($fingerprintCallback);
        $this->defaultsCallback = new Callback($defaultsCallback);

        $this->addHidden(self::FINGERPRINT);
    }

    public function getCustomError() {
        return $this->customError;
    }

    public function setCustomError($customError) {
        $this->customError = $customError;
    }

    public function getRefreshOnConflict() {
        return $this->refreshOnConflict;
    }

    /**
     * Sets whether form values are refreshed when conflict occured.
     * 
     * @param boolean $refreshOnConflict
     */
    public function setRefreshOnConflict($refreshOnConflict) {
        $this->refreshOnConflict = $refreshOnConflict;
    }

    /**
     * @param null $values  Must be always null! Defaults callback is used to produce the values.
     * @param boolean $erase
     * @throws LogicException
     */
    public function setDefaults($values = null, $erase = FALSE) {
        if ($values !== null) {
            throw new LogicException('Default values in ' . __CLASS__ . ' are set by the callback.');
        }

        $defaults = $this->defaultsCallback->invoke();

        parent::setDefaults($defaults, $erase);

        if (!$this->isAnchored() || !$this->isSubmitted()) {
            $this->setFingerprint($this->fingerprintCallback->invoke());
        }
    }

    public function isValid() {
        $receivedFingerprint = $this[self::FINGERPRINT]->getValue();
        $currentFingerprint = $this->fingerprintCallback->invoke();

        if ($receivedFingerprint != $currentFingerprint) {
            $this->addError('Od zobrazení formuláře byla změněna jeho data.');
            $this->setFingerprint($currentFingerprint);

            parent::setValues($this->defaultsCallback->invoke());
            return false;
        }

        return parent::isValid();
    }

    private function setFingerprint($fingerprint) {
        $this[self::FINGERPRINT]->setValue($fingerprint);
    }

}
