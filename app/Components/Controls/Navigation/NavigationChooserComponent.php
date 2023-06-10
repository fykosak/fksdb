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
     */
    final public function renderBoard(string $root, bool $subTitle = false): void
    {
        $structure = $this->navigationFactory->getStructure($root);
        $this->template->items = $this->getItems($structure);
        $this->template->subTitle = $subTitle;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte');
    }

    final public function renderBoardInline(array $items, bool $subTitle = false): void
    {
        $this->template->items = $items;
        $this->template->subTitle = $subTitle;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte');
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
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
        if (isset($item['presenter'])) {
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
        }
        return false;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function getItemTitle(array $item): Title
    {
        if (isset($item['presenter'])) {
            $presenter = $this->presenterBuilder->preparePresenter(
                $item['presenter'],
                $item['action'],
                $item['params'],
                $this->getPresenter()->getParameters()
            );
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return new Title(null, '');
    }

    /**
     * @throws InvalidLinkException
     */
    public function getItemLink(array $item): string
    {
        if (isset($item['presenter'])) {
            return $this->getPresenter()->link(
                ':' . $item['presenter'] . ':' . $item['action'],
                array_merge($this->getPresenter()->getParameters(), $item['params'])
            );
        }
        return '';
    }

    /**
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function isItemVisible(array $item): bool
    {
        if (isset($item['presenter'])) {
            return $this->getPresenter()->authorized(
                ':' . $item['presenter'] . ':' . $item['action'],
                array_merge($this->getPresenter()->getParameters(), $item['params'])
            );
        }
        return true;
    }
}
