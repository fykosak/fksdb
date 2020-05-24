<?php

namespace FKSDB\Components\Forms\Controls\Autocomplete;

use Nette\Application\IPresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IAutocompleteJSONProvider extends IPresenter {

    /**
     * @param $acName
     * @param $acQ
     * @return mixed
     */
    public function handleAutocomplete($acName, $acQ);
}
