<?php

namespace FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Results;

use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;


abstract class Results extends ResultsAndStatistics {

    public function getComponentName(): string {
        return 'results';
    }
}
