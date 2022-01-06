<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Autocomplete;

class ArrayProvider implements FilteredDataProvider {

    private array $data;

    private array $labelById;

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

    /**
     * Prefix search.
     */
    public function getFilteredItems(?string $search): array {
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
     */
    public function setDefaultValue($id): void {
        /* intentionally blank */
    }
}
