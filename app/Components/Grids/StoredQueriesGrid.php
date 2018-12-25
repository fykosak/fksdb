<?php

namespace FKSDB\Components\Grids;

use Authorization\ContestAuthorizator;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use ServiceStoredQuery;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class StoredQueriesGrid extends BaseGrid {
    /** @const No. of characters that are showed from query description. */

    const DESCRIPTION_TRUNC = 80;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    private $isFilteredByTag = false;

    function __construct(ServiceStoredQuery $serviceStoredQuery, ContestAuthorizator $contestAuthorizator) {
        parent::__construct();
        $this->serviceStoredQuery = $serviceStoredQuery;
        $this->contestAuthorizator = $contestAuthorizator;
    }

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
        $this->addColumn('tags', _('Štítky'))->setRenderer(function (\FKSDB\ORM\ModelStoredQuery $row) {
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
        $this->addButton("edit", _("Upravit"))
            ->setText('Upravit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("edit", $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'edit', $contest);
            });
        $this->addButton("show", _("Podrobnosti"))
            ->setText('Podrobnosti')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("show", $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'show', $contest);
            });

        $this->addButton("execute", _("Spustit"))
            ->setClass("btn btn-sm btn-primary")
            ->setText('Spustit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("execute", $row->query_id);
            })
            ->setShow(function ($row) use ($contest) {
                return $this->contestAuthorizator->isAllowed($row, 'show', $contest);
            });

        if ($this->getPresenter()->authorized('compose')) {
            $this->addGlobalButton('compose', 'Napsat dotaz')
                ->setLink($this->getPresenter()->link('compose'));
        }
    }

}
