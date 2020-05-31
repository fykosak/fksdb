<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Forms\OptimisticForm;
use Nette\Application\UI\Form;
use Nette\DI\Container;

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
     * @param Container $container
     * @param callable $fingerprintCallback
     * @param callable $defaultsCallback
     */
    public function __construct(Container $container,callable $fingerprintCallback, callable $defaultsCallback) {
        parent::__construct($container);
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;
    }

    /** @return OptimisticForm */
    protected function createComponentForm(): Form {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
