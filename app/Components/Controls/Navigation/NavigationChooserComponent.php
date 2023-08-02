<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\UI\Navigation\NavigationItemComponent;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;

/**
 * @method BasePresenter getPresenter()
 * @phpstan-type Item array{
 *      'presenter':string,
 *      'action':string,
 *      'params':array<string,int|string|bool|null>,
 *      'fragment':string
 * }
 * @phpstan-type RootItem array{
 *      'presenter':string,
 *      'action':string,
 *      'params':array<string,int|string|bool|null>,
 *      'fragment':string,'parents':Item[]
 * }
 */
final class NavigationChooserComponent extends NavigationItemComponent
{
    private NavigationFactory $navigationFactory;
    private PresenterBuilder $presenterBuilder;

    final public function injectPrimary(NavigationFactory $navigationFactory, PresenterBuilder $presenterBuilder): void
    {
        $this->presenterBuilder = $presenterBuilder;
        $this->navigationFactory = $navigationFactory;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    final public function renderNav(string $root): void
    {
        $structure = $this->navigationFactory->getStructure($root);
        parent::render($this->getItem($structure));
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    final public function renderBoard(string $root, bool $subTitle = false): void
    {
        $structure = $this->navigationFactory->getStructure($root);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte', [
            'items' => $this->getItems($structure),
            'subTitle' => $subTitle,
        ]);
    }

    /**
     * @param NavItem[] $items
     */
    final public function renderBoardInline(array $items, bool $subTitle = false): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte', [
            'items' => $items,
            'subTitle' => $subTitle,
        ]);
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     * @phpstan-param RootItem $structure
     */
    private function getItem(array $structure): NavItem
    {
        return new NavItem($this->getItemTitle($structure), '#', [], $this->getItems($structure));
    }

    /**
     * @return NavItem[]
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws BadRequestException
     * @throws \ReflectionException
     * @phpstan-param RootItem $structure
     */
    private function getItems(array $structure): array
    {
        $items = [];
        foreach ($structure['parents'] as $item) {
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
     * @phpstan-param Item $item
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
     * @phpstan-param Item $item
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
     * @phpstan-param Item $item
     */
    public function isItemVisible(array $item): bool
    {
        return $this->getPresenter()->authorized(
            ':' . $item['presenter'] . ':' . $item['action'],
            array_merge($this->getPresenter()->getParameters(), $item['params'])
        );
    }
}
