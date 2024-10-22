<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\Navigation\NavigationItemComponent;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;

/**
 * @method BasePresenter getPresenter()
 * @phpstan-type TItem array{
 *      presenter:string,
 *      action:string,
 *      params:array<string,scalar|null>,
 * }
 * @phpstan-type TRootItem array{title:Title,items:array<string,scalar[]>}
 */
final class NavigationChooser extends NavigationItemComponent
{
    private PresenterBuilder $presenterBuilder;

    final public function injectPrimary(PresenterBuilder $presenterBuilder): void
    {
        $this->presenterBuilder = $presenterBuilder;
    }

    /**
     * @phpstan-param TRootItem $root
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     * @throws BadRequestException
     */
    final public function renderNavTitle(array $root): void
    {
        $item = new NavItem($root['title'], '#', [], $this->getItems(self::mapArray($root['items'])));
        parent::render($item);
    }

    /**
     * @phpstan-param array<string,scalar[]> $root
     * @return TItem[]
     */
    private static function mapArray(array $root): array
    {
        $items = [];
        foreach ($root as $key => $value) {
            $items[] = self::createNode($key, $value);
        }
        return $items;
    }

    /**
     * @phpstan-param array<string,scalar|null> $params
     * @phpstan-return TItem
     */
    private static function createNode(string $nodeId, array $params): array
    {
        [$module, $presenter, $action] = explode(':', $nodeId);
        return [
            'presenter' => $module . ':' . $presenter,
            'action' => $action,
            'params' => $params,
        ];
    }

    /**
     * @phpstan-param TRootItem $root
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    final public function renderBoard(array $root, bool $subTitle = false): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte', [
            'items' => $this->getItems(self::mapArray($root['items'])),
            'subTitle' => $subTitle,
        ]);
    }

    /**
     * @phpstan-param NavItem[] $items
     */
    final public function renderBoardInline(array $items, bool $subTitle = false): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte', [
            'items' => $items,
            'subTitle' => $subTitle,
        ]);
    }

    /**
     * @phpstan-return NavItem[]
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws BadRequestException
     * @throws \ReflectionException
     * @phpstan-param TItem[] $structure
     */
    private function getItems(array $structure): array
    {
        $items = [];
        foreach ($structure as $item) {
            if ($this->isItemVisible($item)) {
                $items[] = new NavItem(
                    $this->getItemTitle($item),
                    ':' . $item['presenter'] . ':' . $item['action'],
                    $item['params'],
                    [],
                    $this->isItemActive($item)
                );
            }
        }
        return $items;
    }

    /**
     * @phpstan-param TItem $item
     */
    public function isItemActive(array $item): bool
    {
        try {
            $this->getPresenter()->link(
                ':' . $item['presenter'] . ':' . $item['action'],
                array_merge($this->getPresenter()->getParameters(), $item['params'])
            );
        } catch (\Throwable $exception) {
            /* empty */
        }
        $result = $this->getPresenter()->getLastCreatedRequestFlag('current');
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @phpstan-param TItem $item
     */
    public function getItemTitle(array $item): Title
    {
        $presenter = $this->presenterBuilder->preparePresenter(
            $item['presenter'],
            $item['action'],
            $item['params'],
            $this->getPresenter()->getParameters()
        );
        $presenter->setView($presenter->getView()); // to force update the title

        return $presenter->getTitle();
    }

    /**
     * @throws InvalidLinkException
     * @phpstan-param TItem $item
     */
    public function getItemLink(array $item): string
    {
        return $this->getPresenter()->link(
            ':' . $item['presenter'] . ':' . $item['action'],
            array_merge($this->getPresenter()->getParameters(), $item['params'])
        );
    }

    /**
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     * @phpstan-param TItem $item
     */
    public function isItemVisible(array $item): bool
    {
        try {
            return $this->getPresenter()->authorized(
                ':' . $item['presenter'] . ':' . $item['action'],
                array_merge($this->getPresenter()->getParameters(), $item['params'])
            );
        } catch (AbortException $exception) {
            return false;
        }
    }
}
