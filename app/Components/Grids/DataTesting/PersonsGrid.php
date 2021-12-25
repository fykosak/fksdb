<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

class PersonsGrid extends BaseGrid
{

    private ServicePerson $servicePerson;

    private DataTestingFactory $dataTestingFactory;

    final public function injectPrimary(ServicePerson $servicePerson, DataTestingFactory $dataTestingFactory): void
    {
        $this->servicePerson = $servicePerson;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    protected function getData(): IDataSource
    {
        $persons = $this->servicePerson->getTable();
        return new NDataSource($persons);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->addColumns(['person.person_link']);

        foreach ($this->dataTestingFactory->getTests('person') as $test) {
            $this->addColumn($test->id, $test->title)->setRenderer(function ($person) use ($test): Html {
                $logger = new MemoryLogger();
                $test->run($logger, $person);
                return self::createHtmlLog($logger->getMessages());
            });
        }
    }

    /**
     * @param TestLog[] $logs
     * @throws BadTypeException
     * @throws NotImplementedException
     */
    protected static function createHtmlLog(array $logs): Html
    {
        $container = Html::el('span');
        foreach ($logs as $log) {
            if ($log instanceof TestLog) {
                $icon = $log->createHtmlIcon();
            } else {
                throw new BadTypeException(TestLog::class, $log);
            }
            $container->addHtml(
                Html::el('span')->addAttributes([
                    'class' => 'text-' . $log->level,
                    'title' => $log->text,
                ])->addHtml($icon)
            );
        }
        return $container;
    }
}
