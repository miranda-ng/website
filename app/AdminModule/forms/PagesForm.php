<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form;

class PagesForm extends Form
{
	
	public function __construct($parent = null, $name = null) {
		parent::__construct($parent, $name);
		
		$this->addProtection('Timeout for security token. Submit form again.');
		
		$this->addText('title', 'Title:', 70, 255)
			->setRequired('Enter title');
			
		$this->addTextArea('content', 'Content:', 40, 20)
			->setAttribute('class', 'texyla');
		
		$this->addSubmit('submit', 'Save');
		
		$this->onSuccess[] = callback($this, 'formSubmitted');
	}
	
	public function formSubmitted($form) {
		$values = $form->getValues();

		$id = $this->presenter->getParameter('id');
		if (!$id/* || $form->submitted == $form["new"]*/) {
			//$res = $this->presenter->context->database->table('pages')->insert($values);
		} else {
			$res = $this->presenter->context->database->table('pages')->find($id)->update($values);
		}
		
		$this->presenter->flashMessage("Page '$values->title' was saved", 'success');
		$this->presenter->redirect('Pages:');	
	}
	
}
