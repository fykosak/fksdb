<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class StoredQueryTagTypeProvider implements IFilteredDataProvider {

    const DESCRIPTION = 'description';

    /**
     * @var ServiceStoredQueryTagType
     */
    private $serviceStoredQueryTagType;

    /**
     * @var TypedTableSelection
     */
    private $searchTable;

    /**
     * StoredQueryTagTypeProvider constructor.
     * @param ServiceStoredQueryTagType $serviceStoredQueryTagType
     */
    function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
        $this->searchTable = $this->serviceStoredQueryTagType->getTable();
    }

    /**
     * Prefix search.
     *
     * @param string $search
     * @return array
     */
    public function getFilteredItems($search) {
        $search = trim($search);
        $search = str_replace(' ', '', $search);
        $this->searchTable
            ->where('name LIKE concat(?, \'%\') OR description LIKE concat(?, \'%\')', $search, $search);
        return $this->getItems();
    }

    /**
     * @param int $id
     * @return string
     */
    public function getItemLabel(int $id): string {
        /** @var ModelStoredQueryTagType $tagType */
        $tagType = $this->serviceStoredQueryTagType->findByPrimary($id);
        return $tagType->name;
    }

    /**
     * @return array
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
     * @param $id
     */
    public function setDefaultValue($id) {
        /* intentionally blank */
    }

}
