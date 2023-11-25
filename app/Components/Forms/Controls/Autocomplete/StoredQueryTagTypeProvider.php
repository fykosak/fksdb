<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\StoredQuery\TagTypeModel;
use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedSelection;

/**
 * @phpstan-type TItem array{label:string,value:int,description:string|null}
 * @phpstan-implements FilteredDataProvider<TItem>
 */
class StoredQueryTagTypeProvider implements FilteredDataProvider
{
    private TagTypeService $storedQueryTagTypeService;
    /** @phpstan-var TypedSelection<TagTypeModel> */
    private TypedSelection $searchTable;

    public function __construct(TagTypeService $storedQueryTagTypeService)
    {
        $this->storedQueryTagTypeService = $storedQueryTagTypeService;
        $this->searchTable = $this->storedQueryTagTypeService->getTable();
    }

    /**
     * @phpstan-return array<int,TData>
     */
    public function getFilteredItems(?string $search): array
    {
        $search = trim((string)$search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
            ->where('name LIKE concat(?, \'%\') OR description LIKE concat(?, \'%\')', $search, $search);
        return $this->getItems();
    }

    public function getItemLabel(int $id): array
    {
        /** @var TagTypeModel|null $tagType */
        $tagType = $this->storedQueryTagTypeService->findByPrimary($id);
        return [
            'label' => $tagType->name,
            'value' => $tagType->tag_type_id,
            'description' => $tagType->description,
        ];
    }

    /**
     * @phpstan-return array<int,TData>
     */
    public function getItems(): array
    {
        $tagTypes = $this->searchTable->order('name');

        $result = [];
        /** @var TagTypeModel $tagType */
        foreach ($tagTypes as $tagType) {
            $result[] = $this->serializeItem($tagType);
        }
        return $result;
    }

    /**
     * @param TagTypeModel $model
     * @phpstan-return TData
     */
    public function serializeItem(Model $model): array
    {
        return [
            'label' => $model->name,
            'value' => $model->tag_type_id,
            'description' => $model->description,
        ];
    }

    /**
     * @param mixed $id
     */
    public function setDefaultValue($id): void
    {
        /* intentionally blank */
    }
}
