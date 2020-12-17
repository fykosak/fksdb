<?php

namespace FKSDB\Tests\ModelsTests\Export\Formats;

use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Model\ORM\Models\ModelContest;
use FKSDB\Model\ORM\Services\ServiceContest;
use Nette\DI\Container;

class MockSeriesPresenter implements ISeriesPresenter {
    private ModelContest $contest;

    public function __construct(Container $container) {
        $this->contest = $container->getByType(ServiceContest::class)->findByPrimary(1);
    }

    public function getSelectedAcademicYear(): int {
        return 2000;
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
