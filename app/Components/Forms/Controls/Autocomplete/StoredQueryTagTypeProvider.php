<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\StoredQuery\TagTypeModel;
use FKSDB\Models\ORM\Services\StoredQuery\TagTypeService;
use Fykosak\NetteORM\TypedSelection;

class StoredQueryTagTypeProvider implements FilteredDataProvider
{

    private const DESCRIPTION = 'description';
    private TagTypeService $storedQueryTagTypeService;
    private TypedSelection $searchTable;

    public function __construct(TagTypeService $storedQueryTagTypeService)
    {
        $this->storedQueryTagTypeService = $storedQueryTagTypeService;
        $this->searchTable = $this->storedQueryTagTypeService->getTable();
    }

    /**
     * Prefix search.
     */
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
        /** @var TagTypeModel $tagType */
        $tagType = $this->storedQueryTagTypeService->findByPrimary($id);
        return $tagType->name;
    }

    /**
     * @return TagTypeModel[]
     */
    public function getItems(): array
    {
        $tagTypes = $this->searchTable
            ->order('name');

        $result = [];
        /** @var TagTypeModel $tagType */
        foreach ($tagTypes as $tagType) {
            $result[] = [
                self::LABEL => $tagType->name,
                self::VALUE => $tagType->tag_type_id,
                self::DESCRIPTION => $tagType->description,
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
