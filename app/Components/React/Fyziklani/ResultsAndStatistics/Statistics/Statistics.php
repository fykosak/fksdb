<?php

namespace FKSDB\Components\React\Fyziklani\ResultsAndStatistics\Statistics;

use FKSDB\Components\React\Fyziklani\ResultsAndStatistics\ResultsAndStatistics;

abstract class Statistics extends ResultsAndStatistics {

    public final function getComponentName():string {
        return 'statistics';
    }

}
