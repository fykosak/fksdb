<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\StoredQuery;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Components\Controls\StoredQuery\ResultsComponent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Utils\Strings;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class ResultsGrid extends BaseGrid
{

    private StoredQuery $storedQuery;

    public function __construct(StoredQuery $storedQuery, Container $container)
    {
        parent::__construct($container);
        $this->storedQuery = $storedQuery;
    }

    protected function getData(): IDataSource
    {
        return $this->storedQuery;
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;
        try {
            foreach ($this->storedQuery->getColumnNames() as $name) {
                $this->addColumn(str_replace('-', '_', Strings::webalize($name)), $name)->setRenderer(
                    function (\stdClass $row) use ($name) {
                        return ((array)$row)[$name];
                    }
                );
            }
        } catch (\PDOException $exception) {
            // pass, exception should be handled inn parent components
        }

        //
        // operations
        //
        if (!$this->storedQuery->hasQueryPattern()) {
            $qid = $this->storedQuery->getQId();
            if ($qid) {
                $parameters = ['qid' => $qid, 'bc' => null];
                $queryParameters = $this->storedQuery->getParameters();
                foreach ($this->storedQuery->getParameterNames() as $key) {
                    if (array_key_exists($key, $queryParameters)) {
                        $parameters[ResultsComponent::PARAMETER_URL_PREFIX . $key] = $queryParameters[$key];
                    }
                }
                $this->addGlobalButton('qid')
                    ->setLabel(_('Link'))
                    ->setClass('btn btn-sm btn-secondary')
                    ->setLink($this->getPresenter()->link(':Org:Export:execute', $parameters));
            }
        }
    }
}
