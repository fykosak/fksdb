<?php

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Models\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

class NavigationFactory
{

    private array $structure;

    public PresenterBuilder $presenterBuilder;

    public function __construct(PresenterBuilder $presenterBuilder)
    {
        $this->presenterBuilder = $presenterBuilder;
    }

    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    public function getStructure(string $id): array
    {
        return $this->structure[$id];
    }

    /**
     * @return Presenter|NavigablePresenter
     * @throws BadRequestException
     * @throws BadTypeException
     */
    public function preparePresenter(
        Presenter $ownPresenter,
        string $presenterName,
        string $action,
        ?array $providedParams
    ): Presenter {
        $presenter = $this->presenterBuilder->preparePresenter(
            $presenterName,
            $action,
            $providedParams,
            $ownPresenter->getParameters()
        );
        if (!$presenter instanceof NavigablePresenter) {
            throw new BadTypeException(NavigablePresenter::class, $presenter);
        }
        return $presenter;
    }

    /**
     * @throws \ReflectionException
     */
    public function actionParams(Presenter $presenter, string $actionParams, ?array $params): array
    {
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
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws \ReflectionException
     */
    public function createLink(Presenter $presenter, array $node): string
    {
        $linkedPresenter = $this->preparePresenter(
            $presenter,
            $node['linkPresenter'],
            $node['linkAction'],
            $node['linkParams']
        );
        $linkParams = $this->actionParams($linkedPresenter, $node['linkAction'], $node['linkParams']);
        return $presenter->link(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $linkParams);
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function createLinkParams(Presenter $presenter, array $node): array
    {
        $linkedPresenter = $this->preparePresenter(
            $presenter,
            $node['linkPresenter'],
            $node['linkAction'],
            $node['linkParams']
        );
        $linkParams = $this->actionParams($linkedPresenter, $node['linkAction'], $node['linkParams']);
        return [':' . $node['linkPresenter'] . ':' . $node['linkAction'], $linkParams];
    }

    /**
     * @throws BadRequestException
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public function isAllowed(Presenter $presenter, array $node): bool
    {
        $allowedPresenter = $this->preparePresenter(
            $presenter,
            $node['linkPresenter'],
            $node['linkAction'],
            $node['linkParams']
        );
        $allowedParams = $this->actionParams($allowedPresenter, $node['linkAction'], $node['linkParams']);
        return $presenter->authorized(':' . $node['linkPresenter'] . ':' . $node['linkAction'], $allowedParams);
    }
}
