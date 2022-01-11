<?php

namespace FKSDB\Components\Forms;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\HiddenField;

/**
 * Form that uses optimistic locking to control multiple user access.
 */
class OptimisticForm extends Form {

    private const FINGERPRINT = '__fp';

    /** @var callable */
    private $fingerprintCallback;

    /** @var callable */
    private $defaultsCallback;

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
     * @param null $data Must be always null! Defaults callback is used to produce the values.
     * @return static
     * @throws \LogicException
     */
    public function setDefaults($data = null, bool $erase = false): self {
        if ($data !== null) {
            throw new \LogicException('Default values in ' . __CLASS__ . ' are set by the callback.');
        }

        $defaults = ($this->defaultsCallback)();

        parent::setDefaults($defaults, $erase);

        if (!$this->isAnchored() || !$this->isSubmitted()) {
            $this->setFingerprint(($this->fingerprintCallback)());
        }
        return $this;
    }

    private function getFingerprintInput(): HiddenField {
        return $this[self::FINGERPRINT];
    }

    public function isValid(): bool {
        $receivedFingerprint = $this->getFingerprintInput()->getValue();
        $currentFingerprint = ($this->fingerprintCallback)();

        if ($receivedFingerprint != $currentFingerprint) {
            $this->addError(_('There has been a change in the data of this form since it was shown.'));
            $this->setFingerprint($currentFingerprint);
            parent::setValues(($this->defaultsCallback)());
            return false;
        }
        return parent::isValid();
    }

    private function setFingerprint(string $fingerprint): void {
        $this->getFingerprintInput()->setValue($fingerprint);
    }
}
