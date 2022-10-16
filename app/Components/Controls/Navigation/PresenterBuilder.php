<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Navigation;

use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;

class PresenterBuilder
{
    private IPresenterFactory $presenterFactory;
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

        unset($baseParams[Presenter::ACTION_KEY]);
        foreach ($params as $key => $value) {
            $baseParams[$key] = $value;
        }
        $presenter->loadState($baseParams);
        $presenter->changeAction($action);

        return $presenter;
    }

    private function getCachePresenter(string $presenterName): Presenter
    {
        if (!isset($this->presenters[$presenterName])) {
            $this->presenterCache[$presenterName] = $this->presenterFactory->createPresenter($presenterName);
        }
        return $this->presenterCache[$presenterName];
    }
}
