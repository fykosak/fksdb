<?php

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\DataTesting\DataTestingFactory;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Services\ServicePerson;
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

    /** @var ServicePerson */
    private $servicePerson;

    /** @var DataTestingFactory */
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

    protected function getData(): IDataSource {
        $persons = $this->servicePerson->getTable();
        return new NDataSource($persons);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);

        $this->addColumns(['person.person_link']);

        foreach ($this->dataTestingFactory->getTests('person') as $test) {
            $this->addColumn($test->getId(), $test->getTitle())->setRenderer(function ($person) use ($test) {
                $logger = new MemoryLogger();
                $test->run($logger, $person);
                return self::createHtmlLog($logger->getMessages());
            });
        }
    }

    /**
     * @param array $logs
     * @return Html
     * @throws BadTypeException
     * @throws NotImplementedException
     */
    protected static function createHtmlLog(array $logs): Html {

        $container = Html::el('span');
        foreach ($logs as $log) {
            if ($log instanceof TestLog) {
                $icon = $log->createHtmlIcon();
            } else {
                throw new BadTypeException(TestLog::class, $log);
            }
            $container->addHtml(Html::el('span')->addAttributes([
                'class' => 'text-' . $log->getLevel(),
                'title' => $log->getMessage(),
            ])->addHtml($icon));
        }
        return $container;
    }
}
