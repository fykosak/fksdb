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

    public function __construct(callable $fingerprintCallback, callable $defaultsCallback) {
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;

        parent::__construct();
    }

    /**
     * @return OptimisticForm
     */
    protected function createForm(): Form {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
