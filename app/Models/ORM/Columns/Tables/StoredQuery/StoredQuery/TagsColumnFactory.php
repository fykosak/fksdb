<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\StoredQuery\StoredQuery;

use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\StoredQueryTagTypeProvider;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\MetaDataFactory;
use FKSDB\Models\ORM\Models\StoredQuery\QueryModel;
use FKSDB\Models\ORM\Models\StoredQuery\TagModel;
use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\NetteORM\Model;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class TagsColumnFactory extends ColumnFactory
{
    private TagTypeService $storedQueryTagTypeService;

    public function __construct(TagTypeService $storedQueryTagTypeService, MetaDataFactory $metaDataFactory)
    {
        parent::__construct($metaDataFactory);
        $this->storedQueryTagTypeService = $storedQueryTagTypeService;
    }

    /**
     * @param QueryModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $baseEl = Html::el('div');
        /** @var TagModel $tagRow */
        foreach ($model->getTags() as $tagRow) {
            // TODO why ->stored_query_tag_type
            $tag = $tagRow->tag_type;
            $baseEl->addHtml(
                Html::el('span')
                    ->addAttributes([
                        'class' => 'me-2 badge bg-color-' . $tag->color,
                        'title' => $tag->description,
                    ])
                    ->addHtml(Html::el('i')->addAttributes(['class' => 'fas fa-tag me-1']))
                    ->addText($tag->name)
            );
        }
        return $baseEl;
    }

    protected function createFormControl(...$args): BaseControl
    {
        $select = new AutocompleteSelectBox(true, $this->getTitle(), 'tags');
        $select->setDataProvider(new StoredQueryTagTypeProvider($this->storedQueryTagTypeService));
        $select->setMultiSelect(true);
        return $select;
    }
}
