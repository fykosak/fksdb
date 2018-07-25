<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Controls\PersonAccommodationMatrix;
use Nette\Diagnostics\Debugger;

class PersonAccommodationFactory {
    public function __construct(Service) {
    }

    public function createMatrixSelect($options) {
        Debugger::barDump($options);
        return new PersonAccommodationMatrix();

    }
}
