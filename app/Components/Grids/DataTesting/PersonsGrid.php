<?php

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\DataTesting\DataTestingFactory;
use FKSDB\DataTesting\Tests\Person\PersonTest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;
use FKSDB\Exceptions\NotImplementedException;
use Nette\DI\Container;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/***
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\DataTesting
 */
class PersonsGrid extends BaseGrid {
    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var DataTestingFactory
     */
    private $dataTestingFactory;

    /**
     * @param ServicePerson $servicePerson
     * @param DataTestingFactory $dataTestingFactory
     * @return void
     */
    public function injectPrimary(ServicePerson $servicePerson, DataTestingFactory $dataTestingFactory) {
        $this->servicePerson = $servicePerson;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    /**
     * @param \AuthenticatedPresenter $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $persons = $this->servicePerson->getTable();
        $dataSource = new NDataSource($persons);
        $this->setDataSource($dataSource);

        $this->addColumns(['referenced.person_link']);

        foreach ($this->dataTestingFactory->getTests('person') as $test) {
            $this->addColumn($test->getAction(), $test->getTitle())->setRenderer(function ($person) use ($test) {
                $logger = new TestsLogger();
                $test->run($logger, $person);
                return self::createHtmlLog($logger->getLogs());
            });
        }
    }

    protected function getModelClassName(): string {
        return ModelPerson::class;
    }

    /**
     * @param TestLog[] $logs
     * @return Html
     * @throws NotImplementedException
     */
    protected static function createHtmlLog(array $logs): Html {
        $container = Html::el('span');
        foreach ($logs as $log) {
            $icon = Html::el('span');
            switch ($log->getLevel()) {
                case TestLog::LVL_DANGER:
                    $icon->addAttributes(['class' => 'fa fa-close']);
                    break;
                case TestLog::LVL_WARNING:
                    $icon->addAttributes(['class' => 'fa fa-warning']);
                    break;
                case TestLog::LVL_INFO:
                    $icon->addAttributes(['class' => 'fa fa-info']);
                    break;
                case TestLog::LVL_SUCCESS:
                    $icon->addAttributes(['class' => 'fa fa-check']);
                    break;
                default:
                    throw new NotImplementedException(\sprintf('%s is not supported', $log->getLevel()));
            }
            $container->addHtml(Html::el('span')->addAttributes([
                'class' => 'text-' . $log->getLevel(),
                'title' => $log->getMessage(),
            ])->addHtml($icon));
        }
        return $container;
    }
}
