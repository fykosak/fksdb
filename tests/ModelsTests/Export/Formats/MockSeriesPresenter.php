<?php

namespace FKSDB\Tests\ModelsTests\Export\Formats;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceContest;
use FKSDB\Models\YearCalculator;
use FKSDB\Modules\OrgModule\BasePresenter;
use Nette\DI\Container;

class MockSeriesPresenter extends BasePresenter {

    private ModelContest $contest;

    public function __construct(Container $container) {
        parent::__construct();
        $this->contest = $container->getByType(ServiceContest::class)->findByPrimary(1);
    }

    public function getSelectedAcademicYear(): int {
        return YearCalculator::getCurrentAcademicYear();
    }

    /**
     * @return ModelContest|object
     */
    public function getSelectedContest(): ModelContest {
        return $this->contest;
    }

    public function getSelectedSeries(): int {
        return 1;
    }

    public function getSelectedYear(): int {
        return 1;
    }

    /**
     * @param string $message
     * @param string $type
     * @return \stdClass
     */
    public function flashMessage($message, string $type = 'info'): \stdClass {
        return new \stdClass();
    }
}
