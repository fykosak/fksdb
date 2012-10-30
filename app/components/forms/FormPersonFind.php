<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPersonFind extends Form {

    const ID_PERSON = 'person_id';
    const FULLNAME = 'fullname';
    const FIND = 'find';
    const NEW_ID = '';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText(self::FULLNAME, 'Jméno a příjmení');
        $this->addSubmit(self::FIND, 'Najít');

        $this->addRadioList(self::ID_PERSON, 'Osoba')
                ->setItems($this->guessPersons())
                ->setDefaultValue(self::NEW_ID);

        $this->onSuccess[] = array($this, 'handleFind');
    }

    /**
     * This method must be called even when handling wizard step in order to fill
     * radio list with values.
     * 
     * @param \Nette\Application\UI\Form $form
     */
    public function handleFind(Form $form) {        

        $values = $form->getValues();

        $this[self::ID_PERSON]->setItems($this->guessPersons($values[self::FULLNAME]));
        $this[self::ID_PERSON]->setDefaultValue(self::NEW_ID);
    }

    protected function guessPersons($fullname = null) {
        $items = array();
        $items[self::NEW_ID] = '--nová--';

        if (!$fullname) {
            return $items;
        }

        $servicePerson = $this->getPresenter()->context->getService('ServicePerson');
        $persons = $servicePerson->getTable()->order('family_name, other_name');

        $parts = explode(' ', $fullname, 2);

        if (count($parts) == 1) {
            $persons = $persons->where(
                    'family_name LIKE ? OR other_name LIKE ?', "$parts[0]%", "$parts[0]%");
        } else {
            $persons = $persons->where(
                    '(family_name LIKE ? AND other_name LIKE ?) OR (family_name LIKE ? AND other_name LIKE ?)', "$parts[0]%", "$parts[1]%", "$parts[1]%", "$parts[0]%");
        }
        $persons->where(
                'NOT EXISTS (SELECT ct_id FROM contestant WHERE person_id = person.person_id AND contest_id = ? AND year = ?)', $this->getPresenter()->getSelectedContest()->contest_id, $this->getPresenter()->getSelectedYear()
        );


        while ($person = $persons->fetch()) {
            $name = $person->getFullname();

            // --- former contestants ---
            $contestants = $person->getContestants()->order('contest_id, year');
            $contestantsLabel = array();
            $lastContestant = null;
            while ($contestant = $contestants->fetch()) {
                $contestantsLabel[] = $contestant->contest->name . ' ' . $contestant->year;
                $lastContestant = $contestant;
            }
            // try find school from the most recent contestant record
            $location = null;
            if ($lastContestant && $lastContestant->school) {
                $location = $lastContestant->school->name_abbrev;
            };

            // --- spamees (from the most recent spam collection action ---
            $spamees = $person->getSpamees()->order('collection_id DESC');
            $spamee = $spamees->fetch();
            if (!$location && $spamee && $spamee->school) {
                $location = $spamee->school->name_abbrev;
            }

            // --- try at least address ---
            $postContacts = $person->getPostContacts()->where('type = ?', 'P');
            $postContact = $postContacts->fetch();
            if (!$location && $postContact) {
                $location = $postContact->address->city;
            }

            //TODO link to person profile
            if ($location) {
                $items[$person->person_id] = sprintf(
                        "%s (%s): %s", $name, $location, implode(', ', $contestantsLabel));
            } else {
                $items[$person->person_id] = sprintf(
                        "%s %s", $name, implode(', ', $contestantsLabel));
            }
        }

        return $items;
    }

}
