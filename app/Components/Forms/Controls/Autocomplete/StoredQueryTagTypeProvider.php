<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryTagType;
use FKSDB\Models\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use FKSDB\Models\ORM\Tables\TypedTableSelection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagTypeProvider implements FilteredDataProvider {

    private const DESCRIPTION = 'description';

    private ServiceStoredQueryTagType $serviceStoredQueryTagType;

    private TypedTableSelection $searchTable;

    public function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
        $this->searchTable = $this->serviceStoredQueryTagType->getTable();
    }

    /**
     * Prefix search.
     *
     * @param string|null $search
     * @return array
     */
    public function getFilteredItems(?string $search): array {
        $search = trim((string)$search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
            ->where('name LIKE concat(?, \'%\') OR description LIKE concat(?, \'%\')', $search, $search);
        return $this->getItems();
    }

    public function getItemLabel(int $id): string {
        /** @var ModelStoredQueryTagType $tagType */
        $tagType = $this->serviceStoredQueryTagType->findByPrimary($id);
        return $tagType->name;
    }

    /**
     * @return ModelStoredQueryTagType[]
     */
    public function getItems(): array {
        $tagTypes = $this->searchTable
            ->order('name');

        $result = [];
        /** @var ModelStoredQueryTagType $tagType */
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
     * @return void
     */
    public function setDefaultValue($id): void {
        /* intentionally blank */
    }
}
