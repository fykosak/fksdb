<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\StoredQuery\TagTypeModel;
use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\NetteORM\TypedSelection;

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

    public function getFilteredItems(?string $search): array
    {
        $search = trim((string)$search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
            ->where('name LIKE concat(?, \'%\') OR description LIKE concat(?, \'%\')', $search, $search);
        return $this->getItems();
    }

    public function getItemLabel(int $id): string
    {
        /** @var TagTypeModel|null $tagType */
        $tagType = $this->storedQueryTagTypeService->findByPrimary($id);
        return $tagType->name;
    }

    public function getItems(): array
    {
        $tagTypes = $this->searchTable
            ->order('name');

        $result = [];
        /** @var TagTypeModel $tagType */
        foreach ($tagTypes as $tagType) {
            $result[] = [
                'label' => $tagType->name,
                'value' => $tagType->tag_type_id,
                'description' => $tagType->description,
            ];
        }
        return $result;
    }

    /**
     * @param mixed $id
     */
    public function setDefaultValue($id): void
    {
        /* intentionally blank */
    }
}
