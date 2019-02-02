<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics;

use FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;

abstract class Statistics extends ResultsAndStatistics {

    public final function getComponentName():string {
        return 'statistics';
    }

}
