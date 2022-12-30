<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\DataTesting\TestLog;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;

class PersonsGrid extends BaseGrid
{

    private PersonService $personService;

    private DataTestingFactory $dataTestingFactory;

    final public function injectPrimary(PersonService $personService, DataTestingFactory $dataTestingFactory): void
    {
        $this->personService = $personService;
        $this->dataTestingFactory = $dataTestingFactory;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        $this->data = $this->personService->getTable();
        $this->addColumns(['person.person_link']);

        foreach ($this->dataTestingFactory->getTests('person') as $test) {
            $this->addColumn($test->id, new Title(null, $test->title), function ($person) use ($test): Html {
                $logger = new MemoryLogger();
                $test->run($logger, $person);
                return self::createHtmlLog($logger->getMessages());
            });
        }
    }

    /**
     * @param Message[] $logs
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
