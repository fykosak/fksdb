<?php


namespace FKSDB\Components\Controls\FormControl;


use FKSDB\Components\Forms\OptimisticForm;
use Nette\Application\UI\Form;

class OptimisticFormControl extends FormControl {


    /**
     * @var array
     */
    private $fingerprintCallback;

    /**
     * @var array
     */
    private $defaultsCallback;

    public function __construct($fingerprintCallback, $defaultsCallback) {
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;

        parent::__construct();
    }

    protected function createForm(): Form {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
