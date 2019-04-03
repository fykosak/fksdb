<?php

namespace FKSDB\Components\Forms;

use LogicException;
use Nette\Application\UI\Form;

/**
 * Form that uses optimistic locking to control multiple user access.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OptimisticForm extends Form {

    const FINGERPRINT = '__fp';

    /**
     * @var callable
     */
    private $fingerprintCallback;

    /**
     * @var callable
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
     * @param callable $fingerprintCallback returns fingerprint of current version of the data
     * @param callable $defaultsCallback returns current version of data, formatted as an array
     */
    public function __construct(callable $fingerprintCallback, callable $defaultsCallback) {
        parent::__construct();

        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;

        $this->addHidden(self::FINGERPRINT);
    }

    /**
     * @return string
     */
    public function getCustomError() {
        return $this->customError;
    }

    /**
     * @param $customError
     */
    public function setCustomError($customError) {
        $this->customError = $customError;
    }

    /**
     * @return bool
     */
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
     * @param null $values Must be always null! Defaults callback is used to produce the values.
     * @param boolean $erase
     * @throws LogicException
     */
    public function setDefaults($values = null, $erase = FALSE) {
        if ($values !== null) {
            throw new LogicException('Default values in ' . __CLASS__ . ' are set by the callback.');
        }

        $defaults = ($this->defaultsCallback)();

        parent::setDefaults($defaults, $erase);

        if (!$this->isAnchored() || !$this->isSubmitted()) {
            $this->setFingerprint(($this->fingerprintCallback)());
        }
    }

    /**
     * @return bool
     */
    public function isValid() {
        $receivedFingerprint = $this[self::FINGERPRINT]->getValue();
        $currentFingerprint = ($this->fingerprintCallback)();

        if ($receivedFingerprint != $currentFingerprint) {
            $this->addError(_('Od zobrazení formuláře byla změněna jeho data.')); //TODO customize message accordint to refreshOnConflict value
            $this->setFingerprint($currentFingerprint);


            if ($this->getRefreshOnConflict()) {
                parent::setValues(($this->defaultsCallback)());
            }

            return false;
        }

        return parent::isValid();
    }

    /**
     * @param $fingerprint
     */
    private function setFingerprint($fingerprint) {
        $this[self::FINGERPRINT]->setValue($fingerprint);
    }

}
