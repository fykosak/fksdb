<?php

namespace FKSDB\Components\Controls\Chart\ContestantsPerSeries;

use FKSDB\Components\Controls\Chart\IChart;
use FKSDB\Components\React\ReactComponent;
use FKSDB\Model\ORM\Models\ModelContest;
use Nette\DI\Container;

/**
 * Class AbstractContestantsPerSeriesChart
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractPerSeriesChart extends ReactComponent implements IChart {

    protected ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container, 'chart.contestants-per-series');
        $this->contest = $contest;
    }

    public function getControl(): self {
        return $this;
    }
}
