<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form;

class NewsForm extends Form
{
	
	public function __construct($parent = null, $name = null) {
		parent::__construct($parent, $name);
		
		$this->addProtection('Timeout for security token. Submit form again.');
		
		$this->addText('title', 'Title:', 70, 255)
			->setRequired('Enter title');
			
		$this->addText('link', 'URL link:', 70, 255)
			->setRequired('Enter url link');

		$this->addText('date', 'Date:', 20, 20)
			->setRequired('Enter date')
			->setAttribute('class','date')
			->setDefaultValue(date('Y-m-d H:i:s'));
		
		$this->addTextArea('description', 'Description:', 40, 5)
			->setAttribute('class', 'texyla');
		
		$this->addTextArea('content', 'Content:', 40, 20)
			->setAttribute('class', 'texyla');
		
		$this->addSubmit('submit', 'Save');
		
		$this->onSuccess[] = callback($this, 'formSubmitted');
	}
	
	public function formSubmitted($form) {
		$values = $form->getValues();

		$values->date = new \DateTime($values->date);
		
		$id = $this->presenter->getParameter('id');
		if (!$id/* || $form->submitted == $form["new"]*/) {
			$values->author = $this->presenter->user->id;
			$res = $this->presenter->context->database->table('news')->insert($values);
		} else {
			$res = $this->presenter->context->database->table('news')->find($id)->update($values);
		}
		
		$this->presenter->flashMessage("News '$values->title' was saved", 'success');
		$this->presenter->redirect('News:');	
	}
	
}
