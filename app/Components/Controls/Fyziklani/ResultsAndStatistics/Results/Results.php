<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;

/**
 * Class Results
 * @package FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Results
 */
abstract class Results extends ResultsAndStatistics {

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'results';
    }
}
