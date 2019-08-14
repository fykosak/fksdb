<?php

namespace FKSDB\Components\Grids;

use Authorization\ContestAuthorizator;
use Closure;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\ExportPresenter;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueriesGrid extends BaseGrid {
    /** @const No. of characters that are showed from query description. */

    const DESCRIPTION_TRUNC = 80;

    /**
     * @var \FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    private $isFilteredByTag = false;

    /**
     * StoredQueriesGrid constructor.
     * @param \FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery $serviceStoredQuery
     * @param ContestAuthorizator $contestAuthorizator
     */
    function __construct(ServiceStoredQuery $serviceStoredQuery, ContestAuthorizator $contestAuthorizator) {
        parent::__construct();
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * @return Closure
     */
    public function getFilterByTagCallback() {
        return function (array $tagTypeId) {
            if (empty($tagTypeId)) {
                $this->isFilteredByTag = false;
                return;
            }
            $queries = $this->serviceStoredQuery->findByTagType($tagTypeId)->order('name');
            $this->setDataSource(new NDataSource($queries));
            $this->isFilteredByTag = true;
        };
    }

    /**
     * @param ExportPresenter $presenter
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        if (!$this->isFilteredByTag) {
            $queries = $this->serviceStoredQuery->getTable()->order('name');
            $this->setDataSource(new NDataSource($queries));
        }

        //
        // columns
        //
        $this->addColumn('name', _('Název'));
        $this->addColumn('description', _('Popis'))->setTruncate(self::DESCRIPTION_TRUNC);
        $this->addColumn('tags', _('Štítky'))->setRenderer(function (ModelStoredQuery $row) {
            $baseEl = Html::el('div')->addAttributes(['class' => 'stored-query-tags']);
            foreach ($row->getMStoredQueryTags() as $tag) {
                $baseEl->addHtml(Html::el('span')
                    ->addAttributes([
                        'class' => 'badge stored-query-tag stored-query-tag-' . $tag->color,
                        'title' => $tag->description
                    ])
                    ->addText($tag->name));
            }
            return $baseEl;
        })->setSortable(false);

        //
        // operations
        //
        $contest = $presenter->getSelectedContest();
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'edit', $contest);
            });
        $this->addButton('show', _('Podrobnosti'))
            ->setText(_('Podrobnosti'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('show', $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'show', $contest);
            });

        $this->addButton('execute', _('Spustit'))
            ->setClass('btn btn-sm btn-primary')
            ->setText(_('Spustit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('execute', $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'show', $contest);
            });

        if ($presenter->authorized('compose')) {
            $this->addGlobalButton('compose', _('Napsat dotaz'))
                ->setLink($this->getPresenter()->link('compose'));
        }
    }

}
