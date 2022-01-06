<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Forms\OptimisticForm;
use Nette\DI\Container;

class OptimisticFormControl extends FormControl {

    /** @var callable */
    private $fingerprintCallback;

    /** @var callable */
    private $defaultsCallback;

    public function __construct(Container $container, callable $fingerprintCallback, callable $defaultsCallback) {
        parent::__construct($container);
        $this->fingerprintCallback = $fingerprintCallback;
        $this->defaultsCallback = $defaultsCallback;
    }

    protected function createComponentForm(): OptimisticForm {
        return new OptimisticForm($this->fingerprintCallback, $this->defaultsCallback);
    }
}
