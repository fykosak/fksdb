<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\BadRequestException;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\UI\Presenter;

class PresenterBuilder
{
    private IPresenterFactory $presenterFactory;
    /**
     * @phpstan-var array<string,IPresenter>
     */
    private array $presenterCache = [];

    public function __construct(IPresenterFactory $presenterFactory)
    {
        $this->presenterFactory = $presenterFactory;
    }

    /**
     * Provides an instance of a presenter prepared for calling action*, render*, etc. methods.
     * @param bool $newInstance when false all instances of the same class
     * will be the same and only initilization methods are called
     * @throws BadRequestException
     * @phpstan-param array<string,mixed> $params
     * @phpstan-param array<string,mixed> $baseParams
     */
    public function preparePresenter(
        string $presenterName,
        string $action,
        ?array $params = [],
        ?array $baseParams = [],
        bool $newInstance = false
    ): BasePresenter {
        if ($newInstance) {
            $presenter = $this->presenterFactory->createPresenter($presenterName);
        } else {
            $presenter = $this->getCachePresenter($presenterName);
        }
        if (!$presenter instanceof BasePresenter) {
            throw new BadTypeException(BasePresenter::class, $presenter);
        }

        unset($baseParams[Presenter::ACTION_KEY]);
        foreach ($params as $key => $value) {
            $baseParams[$key] = $value;
        }
        $presenter->loadState($baseParams);
        $presenter->changeAction($action);

        return $presenter;
    }

    private function getCachePresenter(string $presenterName): IPresenter
    {
        if (!isset($this->presenters[$presenterName])) {
            $this->presenterCache[$presenterName] = $this->presenterFactory->createPresenter($presenterName);
        }
        return $this->presenterCache[$presenterName];
    }
}
