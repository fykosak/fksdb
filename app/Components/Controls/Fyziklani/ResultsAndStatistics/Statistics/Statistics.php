<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;

/**
 * Class Statistics
 * @package FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics
 */
abstract class Statistics extends ResultsAndStatistics {

    /**
     * @return string
     */
    public final function getComponentName():string {
        return 'statistics';
    }

}
