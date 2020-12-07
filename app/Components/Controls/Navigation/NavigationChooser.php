<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\Choosers\Chooser;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\UI\PageTitle;
use FKSDB\Model\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;

/**
 * Class NavigationChooser
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NavigationChooser extends Chooser {

    private NavigationFactory $navigationFactory;

    protected array $structure;

    final public function injectPrimary(NavigationFactory $navigationFactory): void {
        $this->navigationFactory = $navigationFactory;
    }

    public function render(string $root = ''): void {
        $this->structure = $this->navigationFactory->getStructure($root);
        parent::render();
    }

    public function renderBoard(string $root): void {
        $this->structure = $this->navigationFactory->getStructure($root);
        $this->beforeRender();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.board.latte');
        $this->template->render();
    }

    /**
     * @return Title
     * @throws BadRequestException
     * @throws BadTypeException
     */
    protected function getTitle(): Title {
        if (isset($this->structure['linkPresenter'])) {
            $presenter = $this->navigationFactory->preparePresenter($this->getPresenter(), $this->structure['linkPresenter'], $this->structure['linkAction'], $this->structure['linkParams']);
            $presenter->setView($presenter->getView()); // to force update the title
            return $presenter->getTitle();
        }
        return new PageTitle('');
    }

    protected function getItems(): iterable {
        return $this->structure['parents'];
    }

    /**
     * @param mixed $item
     * @return bool
     */
    public function isItemActive($item): bool {
        if (isset($item['linkPresenter'])) {
            try {
                $this->navigationFactory->createLink($this->getPresenter(), $item);
            } catch (\Exception $exception) {
                /* empty */
            }
            $result = $this->getPresenter()->getLastCreatedRequestFlag("current");
            if ($result) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $item
     * @return Title
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function getItemTitle($item): Title {
        if (isset($item['linkPresenter'])) {
            $presenter = $this->navigationFactory->preparePresenter($this->getPresenter(), $item['linkPresenter'], $item['linkAction'], $item['linkParams']);
            $presenter->setView($presenter->getView()); // to force update the title

            return $presenter->getTitle();
        }
        return new Title('');
    }

    /**
     * @param mixed $item
     * @return string
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function getItemLink($item): string {
        if (isset($item['linkPresenter'])) {
            return $this->navigationFactory->createLink($this->getPresenter(), $item);
        }
        return '';
    }

    /**
     * @param mixed $item
     * @return bool
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function isItemVisible($item): bool {
        if (isset($item['visible'])) {
            return $item['visible'];
        }
        if (isset($item['linkPresenter'])) {
            return $this->navigationFactory->isAllowed($this->getPresenter(), $item);
        }
        return true;
    }
}
