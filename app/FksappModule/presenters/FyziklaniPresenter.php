<?php

namespace Fksapp;

use \Nette\Application\UI\Form;
use \Nette\Utils\Html;


/**
 * TODO remove(?) backlink
*/
class FyziklaniPresenter extends Action {
	/**
	 * @inheritdoc
	*/
	protected function createResultArray() {
		parent::createResultArray();
		$id     = $this->getEventId();
		$person = $this->getUser()->getIdentity();
		$ep     = $person->getEventParticipant()
					->where('action_id = ?', $id);
		if($ep->count() === 0) {
			$action = $this->findActionById($id);
			$action_name = $action[0]['action_name'];
			$this->addActionsWActions($action_name, 'Prihlaska', 'awa', 'newa');
		}
		else {
			$action = $this->findActionById($id);
			$action_name = $action[0]['action_name'];
			$this->addActionsWActions($action_name, 'Upravit', 'awa', 'newa');
			$this->addActionsWActions($action_name, 'Smazat', 'awa', 'dela');
		}
	}

	public function actionDisplay($id) {
		$backlink = $this->getBacklink();
		if($backlink) {
			$id = $this->getEventId();
			#$this->redirect($backlink, $id);
		}
	}

	public function actionDefault() {
		throw new \Nette\ArgumentOutOfRangeException();
	}

	public function actionDela($id) {
		$template    = $this->template;
		$id          = $this->getEventId();
		$menu_action = $this->findActionById($id);
		$person      = $this->getUser()->getIdentity();

		$event_participant = $person->getEventParticipant()
								->where('action_id = ?', $this->getEventId());
		if($event_participant->count() === 0) {
			throw new \Nette\Application\BadRequestException('Action Dela is not available now');
		}	

		$template->event_name = $menu_action[0]['display'];
		$template->year       = $this->getEventYear();

	}

	public function actionNewa($id) {
		$template    = $this->template;
		$id          = $this->getEventId();
		$menu_action = $this->findActionById($id);

		$template->event_name = $menu_action[0]['display'];
		$template->year       = $this->getEventYear();

		$person = $this->getUser()->getIdentity();
		$ep     = $person->getEventParticipant()
					->where('action_id = ?', $id);
		$template->registred_user_message = $ep->count() > 0 ? 'Na tuto akci jsi jiz prihlasen.' : '';
	}

	public function createComponentRegistrationForm() {
		$request        = $this->getRequest();
		$params         = $request->getParameters();
		$presenter_name = $request->getPresenterName();
		$this->saveBacklink($presenter_name . ':' . $params['action']);

		$person_id  = $this->getUser()->getId();
		$person     = $this->getService('ServicePerson')->getTable()
						->where("person_id = $person_id")
						->fetch();
		$person_login = $this->getService('ServiceLogin')->getTable()
						->where("person_id = $person_id")
						->fetch();
		$questions = array(
			0 => array('answer' => 'jablko', 'question' => 'Eva, Newton, červík Pepík', 'hint' => 'Kulaté ovoce'),
			1 => array('answer' => 'kolo', 'question' => 'mlýn, bicykl, štěstí', 'hint' => 'Kulatá věc')
		);
	
		$form = new Form;

		$form->addText('login', 'Přihlašovací jméno')
			->setRequired('Vyplňte jméno, pomocí kterého se chcete přihlašovat.')
	#		->addRule(array($this, 'checkUniqueUsername'), 'Zvolené uživatelské jméno je již obsazené, zvolte prosím jiné.')
			->setDefaultValue($person_login->login)
			->setDisabled();

		$form->addText('display_name', 'Přezdívka')
			->setDefaultValue($person->display_name)
			->setDisabled();

		$form->addText('email', 'Email')
			->setRequired('Vyplňte svůj email')
			->addRule(Form::EMAIL, 'Email má neplatný formát.')
			->setDefaultValue($person_login->email)
			->setDisabled();

		$form->addText('other_name', 'Jméno')
			->setRequired('Vyplňte své křesní jméno')
			->setDefaultValue($person->other_name)
			->setDisabled();

		$form->addText('family_name', 'Příjmení')
			->setRequired('Vyplňte své příjmení')
			->setDefaultValue($person->family_name)
			->setDisabled();

		$event_participant = $person->getEventParticipant()->where("action_id = ?", $this->getEventId())->fetch();
		$note = "";
		if($event_participant) {
			$note = $event_participant->note;
		}
		$form->addTextArea('note', 'Poznámka')
			->setAttribute('rows', 3)
			->setAttribute('cols', 30)
			->setDefaultValue($note);
	
		$session = $this->getSession('regQuestion');
		if (isset($session->question)) {
			$question = $session->question;
		} 
		else {
			$question = $questions[mt_rand(0, count($questions)-1)];
			$session->question = $question;
		}
	
		$label = Html::el()
			->add(Html::el()->setText("Kontrolní otázka" ))
			->add(Html::el('br'))
			->add(Html::el()->setText("Co spojuje následující slova?"))
			->add(Html::el('br'))
			->add(Html::el('em')->setText($question['question']));
	
		$form->addText('question', $label)
			->setRequired('Vyplňte prosím kontrolní otázku, která ověřuje, zda nejste robot.')
			->addRule(Form::EQUAL, "Chybná odpověď\nHint: " . $question['hint'], $question['answer']);
	
		$form->addProtection();
		
		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'registrationFormOnSuccess');
	
		return $form;
	}														

	public function createComponentDeleteEventParticipantForm($id) {
		$request        = $this->getRequest();
		$params         = $request->getParameters();
		$presenter_name = $request->getPresenterName();
		$this->saveBacklink($presenter_name . ':' . $params['action']);

		$person_id  = $this->getUser()->getId();
		$person     = $this->getService('ServicePerson')->getTable()
						->where("person_id = $person_id")
						->fetch();
		$person_login = $this->getService('ServiceLogin')->getTable()
						->where("person_id = $person_id")
						->fetch();
	
		$form = new Form;
		$form->addProtection();

		$alt = array(
			1 => 'Ano, opravdu se chci z akce odhlasit',
			0 => 'Ne, chci se zucastnit'
		);
		$questions = array(
			0 => array('answer' => 25, 'question' => 'Kolik je pet krat pet?', 'hint' => 'čtvrtina ze sta'),
			1 => array('answer' => 0, 'question' => 'Kolik je deset krat nula?', 'hint' => 'hrdina okamžiku')
		);

		$form->addRadioList('del_radio', '', $alt)
			->setDefaultValue(0);

		$session = $this->getSession('delQuestion');
		if (isset($session->question)) {
			$question = $session->question;
		} 
		else {
			$question = $questions[mt_rand(0, count($questions)-1)];
			$session->question = $question;
		}
		$label = Html::el()
			->add(Html::el('em')->setText($question['question']));	
		$form->addText('question', $label)
			->setRequired('Vyplňte prosím kontrolní otázku, která ověřuje, zda nejste robot.')
			->addRule(Form::EQUAL, "Chybná odpověď\nHint: " . $question['hint'], $question['answer']);

		$form->addSubmit('send', 'Odeslat');
		$form->onSuccess[] = array($this, 'deleteEventParticipantFormOnSuccess');

		return $form;
	}

	public function registrationFormOnSuccess($form) {
		$this->getSession('regQuestion')->remove();
		$backlink = $this->getBacklink();
		$this->removeBacklink();

		$values    = $form->getValues();
		$person    = $this->getUser()->getIdentity();
		$person_id = $this->getUser()->getId();

		$service_ep        = $this->getService('ServiceEventParticipant');
		$event_participant = $person->getEventParticipant()
								->where('action_id = ?', $this->getEventId());
		$number_of_rows    = $event_participant->count();
		if($number_of_rows === 0) {
			$data = array (
				'action_id' => $this->getEventId(),
				'person_id' => $person_id,
				'status'    => 0,   # choosen
				'note'      => $values['note'],
				'created'   => 'NOW()' // TODO not working
			);
			$model_ep = $service_ep->createNew($data);
			$service_ep->save($model_ep);
		}
		else if($number_of_rows === 1){
			$row = $event_participant->fetch();
			$model = ModelEventParticipant::CreateFromTableRow($row);
			$model->note = $values['note'];
			$service_ep->save($model);
		}
		else {
			throw new \Nette\FatalErrorException('Data in the database is INCONSISTENT! Pls check database!');
		}

		$this->redirect($backlink, $this->getEventId());
	}

	public function deleteEventParticipantFormOnSuccess($form) {
		$this->getSession('delQuestion')->remove();
		$backlink = $this->getBacklink();
		$this->removeBacklink();

		$values = $form->getValues();
		if($values->del_radio == 1) {
			$person            = $this->getUser()->getIdentity();
			$event_participant = $person->getEventParticipant()
									->where('action_id = ?', $this->getEventId());
			$number_of_rows    = $event_participant->count();
			if($number_of_rows > 1) {
				throw new \Nette\FatalErrorexception('Data in the database is INCONSISTENT! Pls check database!');
			}

			$event_participant_row = $event_participant->fetch();
			if($event_participant_row) {
				$event_participant_row->delete();
			}
			else {
				throw new \Nette\FatalErrorException('Really bad exception. Pls, contact admistrators quickly!!!');
			}
		}

		$this->redirect('awa:display', $this->getEventId());
	}
}
