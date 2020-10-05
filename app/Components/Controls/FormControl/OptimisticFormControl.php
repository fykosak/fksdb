<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Forms\OptimisticForm;
/**
 * Class OptimisticFormControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class OptimisticFormControl extends FormControl {

    /** @var callable */
    private $fingerprintCallback;

    /** @var callable */
    private $defaultsCallback;

    public function __construct(callable $fingerprintCallback, callable $defaultsCallback) {
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;
        parent::__construct();
    }

    protected function createComponentForm(): OptimisticForm {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
