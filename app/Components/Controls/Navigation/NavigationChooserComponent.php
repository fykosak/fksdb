<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\UI\Navigation\NavigationItemComponent;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;

class NavigationChooserComponent extends NavigationItemComponent
{

    private NavigationFactory $navigationFactory;

    protected array $structure;

    final public function injectPrimary(NavigationFactory $navigationFactory): void
    {
        $this->navigationFactory = $navigationFactory;
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException|\ReflectionException
     */
    final public function renderNav(string $root = ''): void
    {
        $structure = $this->navigationFactory->getStructure($root);
        parent::render($this->getItem($structure));
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function beforeRender(): void
    {
        $this->template->items = $this->getItems();
        $this->template->title = $this->getTitle();
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     */
    final public function renderBoard(string $root): void
    {
        $this->structure = $this->navigationFactory->getStructure($root);
        $this->beforeRender();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte');
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function getTitle(): Title
    {
        if (isset($this->structure['linkPresenter'])) {
            $presenter = $this->navigationFactory->preparePresenter(
                $this->getPresenter(),
                $this->structure['linkPresenter'],
                $this->structure['linkAction'],
                $this->structure['linkParams']
            );
            $presenter->setView($presenter->getView()); // to force update the title
            return $presenter->getTitle();
        }
        return new Title('');
    }

    protected function getItems(): iterable
    {
        return $this->structure['parents'];
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    private function getItem(array $structure): NavItem
    {
        $items = [];
        foreach ($structure['parents'] as $item) {
            [$destination, $params] = $this->navigationFactory->createLinkParams($this->getPresenter(), $item);
            if ($this->isItemVisible($item)) {
                $items[] = new NavItem(
                    $this->getItemTitle($item),
                    $destination,
                    $params,
                    [],
                    $this->isItemActive($item)
                );
            }
        }
        return new NavItem($this->getItemTitle($structure), '#', [], $items);
    }

    public function isItemActive(array $item): bool
    {
        if ($item instanceof NavItem) {
            return false;
        }
        if (isset($item['linkPresenter'])) {
            try {
                $this->navigationFactory->createLink($this->getPresenter(), $item);
            } catch (\Exception $exception) {
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
        if (isset($item['linkPresenter'])) {
            $presenter = $this->navigationFactory->preparePresenter(
                $this->getPresenter(),
                $item['linkPresenter'],
                $item['linkAction'],
                $item['linkParams']
            );
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return new Title('');
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function getItemLink(array $item): string
    {
        if (isset($item['linkPresenter'])) {
            return $this->navigationFactory->createLink($this->getPresenter(), $item);
        }
        return '';
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function isItemVisible(array $item): bool
    {
        if ($item instanceof NavItem) {
            return true;
        }
        if (isset($item['visible'])) {
            return $item['visible'];
        }
        if (isset($item['linkPresenter'])) {
            return $this->navigationFactory->isAllowed($this->getPresenter(), $item);
        }
        return true;
    }
}
