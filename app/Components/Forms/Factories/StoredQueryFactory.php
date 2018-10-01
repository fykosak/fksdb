<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\StoredQueryTagTypeProvider;
use FKSDB\Components\Forms\Controls\SQLConsole;
use Kdyby\Extension\Forms\Replicator\Replicator;
use ModelStoredQuery;
use ModelStoredQueryParameter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use ServiceStoredQueryTagType;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    /**
     * @var ServiceStoredQueryTagType
     */
    private $serviceStoredQueryTagType;

    function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    public function createConsole($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $control = new SQLConsole('SQL');
        $container->addComponent($control, 'sql');

        return $container;
    }

    public function createMetadata($options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
                ->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
                ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);

        $container->addText('qid', _('QID'))
                ->setOption('description', _('Dotazy s QIDem nelze smazat a QID lze použít pro práva a trvalé odkazování.'))
                ->addCondition(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 16)
                ->addRule(Form::REGEXP, _('QID může být jen z písmen anglické abecedy a číslic a tečky.'), '/^[a-z][a-z0-9.]*$/i');

        $container->addComponent($this->createTagSelect(false, _('Štítky'), new StoredQueryTagTypeProvider($this->serviceStoredQueryTagType)), 'tags');

        $container->addTextArea('description', _('Popis dotazu'));

        $container->addText('php_post_proc', _('PHP post processing'))
                ->setOption('description', _('Název třídy pro zpracování výsledku v PHP. Lze upravit jen v databázi.'))
                ->setDisabled();


        return $container;
    }

    public function createParametersMetadata($options = 0, ControlGroup $group = null) {
        $replicator = new Replicator(function($replContainer) use ($group) {
                    $this->buildParameterMetadata($replContainer, $group);

                    $submit = $replContainer->addSubmit('remove', _('Odebrat parametr'));
                    $submit->getControlPrototype()->addClass('btn-danger');
                    $submit->getControlPrototype()->addClass('btn-sm'); // TODO doesn't work
                    $submit->addRemoveOnClick();
                }, 0, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\ModelContainer';
        $replicator->setCurrentGroup($group);
        $submit = $replicator->addSubmit('addParam', _('Přidat parametr'));
        $submit->getControlPrototype()->addClass('btn-sm btn-success');

        $submit->setValidationScope(false)
                ->addCreateOnClick();

        return $replicator;
    }

    /**
     * @internal
     * @param Container $container
     * @param type $group
     */
    public function buildParameterMetadata(Container $container, $group) {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
                ->addRule(Form::FILLED, _('Název parametru musí být vyplněn.'))
                ->addRule(Form::MAX_LENGTH, _('Název parametru je moc dlouhý.'), 16)
                ->addRule(Form::REGEXP, _('Název parametru může být jen z malých písmen anglické abecedy, číslic nebo podtržítka.'), '/^[a-z][a-z0-9_]*$/');

        $container->addText('description', _('Popis'));

        $container->addSelect('type', _('Datový typ'))
                ->setItems([
                    ModelStoredQueryParameter::TYPE_INT => 'integer',
                    ModelStoredQueryParameter::TYPE_STR => 'string',
                    ModelStoredQueryParameter::TYPE_BOOL => 'bool',
                ]);

        $container->addText('default', _('Výchozí hodnota'));
    }

    public function createParametersValues(ModelStoredQuery $queryPattern, $options = 0, ControlGroup $group = null) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        foreach ($queryPattern->getParameters() as $parameter) {
            $name = $parameter->name;
            $subcontainer = new ModelContainer();
            $container->addComponent($subcontainer,$name);
            // $subcontainer = $container->addContainer($name);

            switch ($parameter->type) {
                case ModelStoredQueryParameter::TYPE_INT:
                case ModelStoredQueryParameter::TYPE_STR:
                    $valueElement = $subcontainer->addText('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                        $valueElement->addRule(Form::INTEGER, _('Parametr %label je číselný.'));
                    }

                    $valueElement->setDefaultValue($parameter->getDefaultValue());
                    break;
                case ModelStoredQueryParameter::TYPE_BOOL:
                    $valueElement = $subcontainer->addCheckbox('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    $valueElement->setDefaultValue((bool) $parameter->getDefaultValue());
                    break;
            }
        }

        return $container;
    }

    private function createTagSelect($ajax, $label, IDataProvider $dataProvider, $renderMethod = null) {
        if ($renderMethod === null) {
            $renderMethod = '$("<li>")
                        .append("<a>" + item.label + "<br>" + item.description + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        $select->setMultiselect(true);
        return $select;
    }

}
