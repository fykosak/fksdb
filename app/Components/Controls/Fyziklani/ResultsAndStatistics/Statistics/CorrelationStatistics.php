<?php

namespace FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics;

/**
 * Class CorrelationStatistics
 * @package FKSDB\Components\Controls\Fyziklani\ResultsAndStatistics\Statistics
 */
class CorrelationStatistics extends Statistics {
    /**
     * @return string
     */
    public function getMode(): string {
        return 'correlation';
    }
}
