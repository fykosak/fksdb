<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelContest;
use Nette\Application\UI\Control;
use Nette\DI\Container;

/**
 * Class AbstractContestantsPerSeriesChart
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractContestantsPerSeriesChart extends ReactComponent implements IChart {

    protected ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container, 'chart.contestants-per-series');
        $this->contest = $contest;
    }

    public function getControl(): Control {
        return $this;
    }
}
