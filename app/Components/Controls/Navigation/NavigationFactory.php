<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Components\Controls\PresenterBuilder;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * Class NavigationFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class NavigationFactory {

    private array $structure;

    public PresenterBuilder $presenterBuilder;

    public function __construct(PresenterBuilder $presenterBuilder) {
        $this->presenterBuilder = $presenterBuilder;
    }

    public function setStructure(array $structure): void {
        $this->structure = $structure;
    }

    public function getStructure(string $id): array {
        return $this->structure[$id];
    }

    /**
     * @param Presenter $ownPresenter
     * @param string $presenterName
     * @param string $action
     * @param array|null $providedParams
     * @return Presenter|INavigablePresenter
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function preparePresenter(Presenter $ownPresenter, string $presenterName, string $action, ?array $providedParams): Presenter {
        $presenter = $this->presenterBuilder->preparePresenter($presenterName, $action, $providedParams, $ownPresenter->getParameters());
        if (!$presenter instanceof INavigablePresenter) {
            throw new BadTypeException(INavigablePresenter::class, $presenter);
        }
        return $presenter;
    }

    /**
     * @param Presenter $presenter
     * @param string $actionParams
     * @param array|null $params
     * @return array
     * @throws \ReflectionException
     */
    public function actionParams(Presenter $presenter, string $actionParams, ?array $params): array {
        $method = $presenter->publicFormatActionMethod($actionParams);

        $actionParams = [];
        $rc = new \ReflectionClass($presenter);
        if ($rc->hasMethod($method)) {
            $rm = new \ReflectionMethod($presenter, $method);
            foreach ($rm->getParameters() as $param) {
                $name = $param->getName();
                $actionParams[$name] = $params[$name];
            }
        }
        return $actionParams;
    }


    /**
     * @param Presenter $presenter
     * @param array $node
     * @return string
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function createLink(Presenter $presenter, array $node): string {
        $linkedPresenter = $this->preparePresenter($presenter, $node['linkPresenter'], $node['linkAction'], $node['linkParams']);
        $linkParams = $this->actionParams($linkedPresenter, $node['linkAction'], $node['linkParams']);
        return $presenter->link(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $linkParams);
    }

    /**
     * @param Presenter $presenter
     * @param array $node
     * @return bool
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function isAllowed(Presenter $presenter, array $node): bool {
        $allowedPresenter = $this->preparePresenter($presenter, $node['linkPresenter'], $node['linkAction'], $node['linkParams']);
        $allowedParams = $this->actionParams($allowedPresenter, $node['linkAction'], $node['linkParams']);
        return $presenter->authorized(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $allowedParams);
    }
}
