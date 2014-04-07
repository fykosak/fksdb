<?php

namespace FKSDB\Components\Grids;

use Exports\StoredQuery;
use PDOException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueryGrid extends BaseGrid {

    const PARAMETER_URL_PREFIX = 'p_';

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    function __construct(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        $this->setDataSource($this->storedQuery);

        //
        // columns
        //
        try {
            $c = 0;
            foreach ($this->storedQuery->getColumnNames() as $name) {
                $this->addColumn($c + 1, $name)->setRenderer(function($row) use($c) {
                            echo $row[$c];
                        });
                ++$c;
            }
        } catch (PDOException $e) {
            // pass, exception should be handled inn parent components
        }

        //
        // operations
        //
        $this->paginate = false;

        $this->addGlobalButton('csv')
                ->setLabel('Uložit CSV')
                ->setLink($this->getParent()->link('csv!'));

        $this->addGlobalButton('csvh')
                ->setLabel('Uložit CSV (bez hlavičky)')
                ->setLink($this->getParent()->link('csv!', array('header' => false)));

        if (!$this->storedQuery->getQueryPattern()->isNew()) {
            $this->addGlobalButton('show')
                    ->setLabel(_('Podrobnosti dotazu'))
                    ->setClass('btn btn-sm btn-default')
                    ->setLink($this->getPresenter()->link('Export:show', $this->storedQuery->getQueryPattern()->getPrimary()));
            if ($qid = $this->storedQuery->getQueryPattern()->qid) { // intentionally =
                $parameters = array('qid' => $qid, 'bc' => null);
                $queryParameters = $this->storedQuery->getParameters();
                foreach ($this->storedQuery->getParameterNames() as $key) {
                    if (array_key_exists($key, $queryParameters)) {
                        $parameters[self::PARAMETER_URL_PREFIX . $key] = $queryParameters[$key];
                    }
                }
                $this->addGlobalButton('qid')
                        ->setLabel(_('Odkaz'))
                        ->setClass('btn btn-sm btn-default')
                        ->setLink($this->getPresenter()->link('Export:execute', $parameters));
            }
        }
    }

}
