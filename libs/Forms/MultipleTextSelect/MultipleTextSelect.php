<?php

/**
 *
 * @author   Michal Koutny
 */

namespace OOB;

use Nette;
use Nette\Forms;
use OOB\Forms\IItemsModel;
use OOB\Forms\IItemsModel as IItemsModel2;

/**
 * Form control for multiple selects via
 * text fields.
 * It's necesssary to use VALID rule in order
 * to insert new values into model (in N_INSERT
 * mode).
 */
class MultipleTextSelect extends Forms\Controls\BaseControl {
    /* Unknown values are ignored. */

    const N_IGNORE = 0;

    /* Unknown values causes control to be invalid. */
    const N_INVALID = 1;

    /* Unknown values are inserted into model */
    const N_INSERT = 2;

    /* Consts for HTML 5 element */
    const HTML_DELIMITER = 'mt-delimiter';
    const HTML_ITEMS = 'mt-data';

    /** @var     array (string)    */
    protected $cachedValue = null;

    /** @var     string            value entered by user (unfiltered) */
    //protected $rawValue;
    /** @var IItemsModel2	       model to convert between ids and text values (also should insert new textvalues or return null)
     * 			       and also getting all items */
    private $itemsModel;

    /** @var     string            class name */
    private $className = 'mtselect';

    /** @var     string */
    private $delimiterOwn = ', ';

    /** @var     string */
    private $delimiterMask = ',\s*';

    /**
     * @var int	  mode of operation with unknown items
     */
    private $unknownMode = self::N_IGNORE;

    /**
     * Class constructor.
     *
     * @param    string            label
     */
    public function __construct(IItemsModel $model, $label = NULL) {
        parent::__construct($label);
        $this->control->type = 'text';
        $this->itemsModel = $model;
    }

    /**
     * Returns class name.
     *
     * @return   string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * Sets class name for input element.
     *
     * @param    string
     * @return   self
     */
    public function setClassName($className) {
        $this->className = $className;
        return $this;
    }

    /**
     * Returns items model.
     *
     * @return   IItemsModel2
     */
    public function getItemsModel() {
        return $this->itemsModel;
    }

    /**
     * Sets items model.
     *
     * @param    IItemsModel2
     * @return   self
     */
    public function setItemsModel(Forms\IItemsModel $itemsModel) {
        $this->itemsModel = $itemsModel;
        return $this;
    }

    /**
     *
     * @param string $regexp
     * @return MultipleTextSelect
     */
    public function setDelimiterMask($regexp) {
        $this->delimiterMask = $regexp;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDelimiterMask() {
        return $this->delimiterMask;
    }

    /**
     *
     * @param string $regexp
     * @return MultipleTextSelect
     */
    public function setDelimiterOwn($regexp) {
        $this->delimiterOwn = $regexp;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDelimiterOwn() {
        return $this->delimiterOwn;
    }

    /**
     * @param bool
     * @return MultipleTextSelect
     */
    public function setUnknownMode($value) {
        $this->unknownMode = $value;
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function getUnknownMode() {
        return $this->unknownMode;
    }

    /**
     * @param int
     * @return MultipleTextSelect
     */
    public function setSize($value) {
        $this->control->cols = $value;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getSize() {
        return $this->control->cols;
    }

    /**
     * Generates control's HTML element.
     *
     * @return   Nette\Utils\Html
     */
    public function getControl() {
        $control = parent::getControl();

        if ($this->value) {
            $control->value = $this->value;
        }

        $control->class = $this->className;
        $control->data[self::HTML_DELIMITER] = $this->delimiterMask;
        //$control->data[self::HTML_ITEMS] = htmlspecialchars(json_encode($this->itemsModel->GetAllItems()), ENT_QUOTES);
        $control->data[self::HTML_ITEMS] = json_encode(array_values($this->itemsModel->GetAllItems()));

        return $control;
    }

    /**
     * Sets element value
     *
     * @param    array|string of int
     * @return   self
     */
    public function setValue($value) {
        $this->cachedValue = null;
        $this->value = "";

        
        if (\is_array($value)) {  //list of IDs
            $i = 0;
            foreach ($value as $item) {
                $this->value .= ( ($i != 0) ? $this->delimiterOwn : '') . $this->itemsModel->IdToName($item);
                $i++;
            }
        } elseif (\is_string($value)) {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * @return  array	of int (ids)
     */
    public function getValue() {
        //doesn't insert new items (should have been done in valiadateValid)
        if ($this->cachedValue === null) {
            $this->cachedValue = array();
            $values = \preg_split("/{$this->delimiterMask}/", $this->value);
            $usedValues = array();

            foreach ($values as $item) {
                $item = trim($item);
                if ($item == "")
                    continue;

                $id = $this->itemsModel->NameToId($item, false);

                if ($id !== null) {
                    $this->cachedValue[] = $id;
                }

                $usedValues[$item] = true;
            }
        }

        return $this->cachedValue;
    }

    /**
     * Returns text value.
     *
     * @return   string
     */
    public function getRawValue() {
        return $this->value;
    }

    /**
     * Does user enter anything? (the value doesn't have to be valid)
     *
     * @param    self
     * @return   bool
     */
    public static function validateFilled(Forms\IControl $control) {
        echo "Ptám se na vyplnění.";
        if (!$control instanceof self)
            throw new Nette\InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');

        return count($control->getValue()) > 0;
    }

    /**
     * Is entered value valid? (empty value is also valid!)
     *
     * @param    self
     * @return   bool
     */
    public static function validateValid(Forms\IControl $control) {
        //zvalidovat tak, aby se případně založily nové položky
        if (!$control instanceof self)
            throw new Nette\InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');
//--
        $values = \preg_split("/{$control->delimiterMask}/", $control->value);
        $control->value = "";
        $usedValues = array();
        $valid = true;
        $i = 0;
        foreach ($values as $item) {
            $item = trim($item);
            if ($item == "")
                continue;

            if (array_key_exists($item, $usedValues))  //ignore multiple items
                continue;

            $newId = $control->itemsModel->NameToId($item, $control->unknownMode == self::N_INSERT);

            if ($control->unknownMode == self::N_INVALID && $newId == null) {
                $valid = false;
            }

            $control->value .= ( ($i == 0) ? '' : $control->delimiterOwn ) . $item;

            $usedValues[$item] = true;
            $i++;
        }
//--

        return $valid;
    }

}
