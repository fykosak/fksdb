<?php

namespace FKSDB\Components\Forms\Controls;


use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

class DataListTextInput extends TextInput {

    public function setItems($name, array $items) {
        $dataList = Html::el('datalist')->addAttributes(['id' => $name]);
        foreach ($items as $item) {
            $dataList->add(Html::el('option')->add($item));
        }
        $this->getControlPrototype()->addAttributes(['datalist' => $name]);
        $this->getLabelPrototype()->add($dataList);
    }

}
