<?php

namespace FKSDB\DBReflection\ColumnFactories\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\StoredQueryTagTypeProvider;
use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\DBReflection\MetaDataFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

/**
 * Class TagsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TagsRow extends DefaultColumnFactory {

    private ServiceStoredQueryTagType $serviceStoredQueryTagType;

    public function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType, MetaDataFactory $metaDataFactory) {
        parent::__construct($metaDataFactory);
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    /**
     * @param AbstractModelSingle|ModelStoredQuery $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $baseEl = Html::el('div')->addAttributes(['class' => 'stored-query-tags']);
        foreach ($model->getTags() as $tagRow) {
            // TODO why ->stored_query_tag_type
            $tag = ModelStoredQueryTagType::createFromActiveRow($tagRow->tag_type);
            $baseEl->addHtml(Html::el('span')
                ->addAttributes([
                    'class' => 'badge stored-query-tag stored-query-tag-' . $tag->color,
                    'title' => $tag->description,
                ])
                ->addText($tag->name));
        }
        return $baseEl;
    }

    protected function createFormControl(...$args): BaseControl {
        $select = new AutocompleteSelectBox(true, $this->getTitle(), 'tags');
        $select->setDataProvider(new StoredQueryTagTypeProvider($this->serviceStoredQueryTagType));
        $select->setMultiSelect(true);
        return $select;
    }
}
