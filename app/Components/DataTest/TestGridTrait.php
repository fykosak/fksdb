<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Utils\Html;

/**
 * @template TModel of Model
 */
trait TestGridTrait
{
    /**
     * @phpstan-param (Test<TModel>)[] $tests
     */
    protected function addTests(array $tests): void
    {
        foreach ($tests as $index => $test) {
            /** @phpstan-var RendererItem<TModel> $item */
            $item = new RendererItem(
                $this->container,
                function (Model $person) use ($test): Html {
                    $logger = new TestLogger();
                    /** @phpstan-var TModel $person */
                    $test->run($logger, $person);
                    return self::createHtmlLog($logger);
                },
                $test->getTitle()
            );
            $this->addTableColumn($item, 'test_' . $index);
        }
    }

    /**
     * @throws NotImplementedException
     */
    private static function createHtmlLog(TestLogger $logger): Html
    {
        $container = Html::el('span');
        $messages = $logger->getMessages();
        if (count($messages)) {
            foreach ($messages as $log) {
                $container->addHtml(self::createHtmlIcon($log));
            }
        } else {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes([
                        'class' => 'text-success',
                        'title' => _('No errors found'),
                    ])
                    ->addHtml(
                        Html::el('i')->addAttributes([
                            'class' => 'fas fa-check',
                        ])
                    )
            );
        }
        return $container;
    }

    /**
     * @throws NotImplementedException
     */
    private static function mapLevelToIcon(TestMessage $message): string
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
    private static function createHtmlIcon(TestMessage $message): Html
    {
        return Html::el('span')
            ->addAttributes([
                'class' => 'text-' . $message->level,
                'title' => $message->toText(),
            ])->addHtml(
                Html::el('i')
                    ->addAttributes([
                        'class' => self::mapLevelToIcon($message),
                    ])
            );
    }
}
