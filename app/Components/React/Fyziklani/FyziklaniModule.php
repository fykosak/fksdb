<?php

namespace FKSDB\Components\React\Fyziklani;


use FKSDB\Components\React\ReactComponent;

abstract class FyziklaniModule extends ReactComponent {

    public final function getModuleName(): string {
        return 'fyziklani';
    }
}
