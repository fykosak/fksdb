<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;

abstract class Results extends ResultsAndStatistics {

    public function getComponentName(): string {
        return 'results';
    }
}
