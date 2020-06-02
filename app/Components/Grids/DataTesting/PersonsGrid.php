<?php

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\DataTesting\DataTestingFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\DataTesting\TestsLogger;
use FKSDB\DataTesting\TestLog;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonsGrid extends BaseGrid {

    private ServicePerson $servicePerson;

    private DataTestingFactory $dataTestingFactory;

    public function injectPrimary(ServicePerson $servicePerson, DataTestingFactory $dataTestingFactory): void {
        $this->servicePerson = $servicePerson;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    protected function getData(): IDataSource {
        $persons = $this->servicePerson->getTable();
        return new NDataSource($persons);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addColumns(['person.person_link']);

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
