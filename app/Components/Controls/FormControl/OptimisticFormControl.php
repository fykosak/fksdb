<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Forms\OptimisticForm;
use Nette\Application\UI\Form;

/**
 * Class OptimisticFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OptimisticFormControl extends FormControl {

    /** @var callable */
    private $fingerprintCallback;

    /** @var callable */
    private $defaultsCallback;

    /**
     * OptimisticFormControl constructor.
     * @param callable $fingerprintCallback
     * @param callable $defaultsCallback
     */
    public function __construct(callable $fingerprintCallback, callable $defaultsCallback) {
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;

        parent::__construct();
    }

    /** @return OptimisticForm */
    protected function createComponentForm(): Form {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
