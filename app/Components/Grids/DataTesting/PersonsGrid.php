<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\DataTesting;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\DataTesting\DataTestingFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\TypedSelection;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;
use Nette\Utils\Html;

/**
 * @phpstan-extends BaseGrid<PersonModel>
 */
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
     * @phpstan-return TypedSelection<PersonModel>
     */
    protected function getModels(): TypedSelection
    {
        return $this->personService->getTable();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns(['person.person_link']);

        foreach ($this->dataTestingFactory->getTests('person') as $id => $test) {
            $this->addColumn(
                new RendererItem(
                    $this->container,
                    function (PersonModel $person) use ($test): Html {
                        $logger = new MemoryLogger();
                        $test->run($logger, $person);
                        return self::createHtmlLog($logger->getMessages());
                    },
                    new Title(null, $test->title)
                ),
                $id
            );
        }
    }

    /**
     * @phpstan-param Message[] $logs
     * @throws NotImplementedException
     */
    protected static function createHtmlLog(array $logs): Html
    {
        $container = Html::el('span');
        foreach ($logs as $log) {
            $icon = self::createHtmlIcon($log);
            $container->addHtml(
                Html::el('span')->addAttributes([
                    'class' => 'text-' . $log->level,
                    'title' => $log->text,
                ])->addHtml($icon)
            );
        }
        return $container;
    }
    /**
     * @throws NotImplementedException
     */
    public static function mapLevelToIcon(Message $message): string
    {
        switch ($message->level) {
            case Message::LVL_ERROR:
                return 'fas fa-times';
            case Message::LVL_WARNING:
                return 'fas fa-warning';
            case Message::LVL_INFO:
                return 'fas fa-info';
            case Message::LVL_SUCCESS:
                return 'fas fa-check';
            default:
                throw new NotImplementedException(\sprintf('Level "%s" is not supported', $message->level));
        }
    }

    /**
     * @throws NotImplementedException
     */
    public static function createHtmlIcon(Message $message): Html
    {
        $icon = Html::el('span');
        $icon->addAttributes([
            'class' => self::mapLevelToIcon($message),
        ]);
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $message->level,
            'title' => $message->text,
        ])->addHtml($icon);
    }
}
