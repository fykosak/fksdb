<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ArrayProvider implements IFilteredDataProvider {

    private array $data;
    /**
     * @var array
     */
    private $labelById;

    /**
     * ArrayProvider constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = [];
        $this->labelById = $data;
        foreach ($data as $id => $label) {
            $this->data[] = [
                self::VALUE => $id,
                self::LABEL => $label,
            ];
        }
    }

    public function getFilteredItems(string $search): array {
        $result = [];
        foreach ($this->data as $item) {
            $label = $item[self::LABEL];
            if (mb_substr($label, 0, mb_strlen($search)) == $search) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function getItemLabel(int $id): string {
        return $this->labelById[$id];
    }

    public function getItems(): array {
        return $this->data;
    }

    /**
     * @param mixed $id
     * @return void
     */
    public function setDefaultValue($id): void {
        /* intentionally blank */
    }
}
