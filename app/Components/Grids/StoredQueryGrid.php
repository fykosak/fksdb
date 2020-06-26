<?php

namespace FKSDB\Components\Grids;

use FKSDB\Exports\ExportFormatFactory;
use FKSDB\StoredQuery\StoredQuery;
use FKSDB\Components\Controls\ResultsComponent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use Nette\Utils\Strings;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use PDOException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueryGrid extends BaseGrid {

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var ExportFormatFactory
     */
    private $exportFormatFactory;

    /**
     * StoredQueryGrid constructor.
     * @param StoredQuery $storedQuery
     * @param Container $container
     */
    public function __construct(StoredQuery $storedQuery, Container $container) {
        parent::__construct($container);
        $this->storedQuery = $storedQuery;
    }

    /**
     * @param ExportFormatFactory $exportFormatFactory
     * @return void
     */
    public function injectExportFormatFactory(ExportFormatFactory $exportFormatFactory) {
        $this->exportFormatFactory = $exportFormatFactory;
    }

    protected function getData(): IDataSource {
        return $this->storedQuery;
    }

    /**
     * @param Presenter $presenter
     * @throws InvalidLinkException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        try {
            foreach ($this->storedQuery->getColumnNames() as $name) {
                $this->addColumn(str_replace('-', '_', Strings::webalize($name)), $name)->setRenderer(function (\stdClass $row) use ($name) {
                    return ((array)$row)[$name];
                });
            }
        } catch (PDOException $exception) {
            // pass, exception should be handled inn parent components
        }

        //
        // operations
        //
        if (!$this->storedQuery->hasQueryPattern()) {
            $qid = $this->storedQuery->getQId();;
            if ($qid) {
                $parameters = ['qid' => $qid, 'bc' => null];
                $queryParameters = $this->storedQuery->getParameters();
                foreach ($this->storedQuery->getParameterNames() as $key) {
                    if (array_key_exists($key, $queryParameters)) {
                        $parameters[ResultsComponent::PARAMETER_URL_PREFIX . $key] = $queryParameters[$key];
                    }
                }
                $this->addGlobalButton('qid')
                    ->setLabel(_('Odkaz'))
                    ->setClass('btn btn-sm btn-secondary')
                    ->setLink($this->getPresenter()->link('Export:execute', $parameters));
            }
        }
    }

}
