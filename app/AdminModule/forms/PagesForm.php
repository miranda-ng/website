<?php

namespace AdminModule\Forms;

use Nette\Application\UI\Form;

class PagesForm extends Form
{
	/** @var GettextTranslator\Gettext */
    public $translator;

	public function __construct($parent = null, $name = null, $translator) {
		parent::__construct($parent, $name);
		$this->translator = $translator;

		$this->setTranslator($this->translator);

		$this->addProtection('Timeout for security token. Submit form again.');

		$this->addText('title', 'Title:', 70, 255)
			->setRequired('Enter title');

		$this->addTextArea('content', 'Content:', 40, 20)
			->setAttribute('class', 'texyla');

		$this->addSubmit('submit', 'Save');

		$this->onSuccess[] = $this->formSubmitted;
	}

	public function formSubmitted($form) {
		$values = $form->getValues();

		$id = $this->presenter->getParameter('id');
		if (!$id) {
			//$res = $this->presenter->context->database->table('pages')->insert($values);
		} else {
			$res = $this->presenter->context->database->table('pages_content')->where("pages_id", $id)->where("lang", $this->presenter->lang)->fetch();
			if ($res) {
				$res->update($values);
			} else {
				$values["pages_id"] = $id;
				$values["lang"] = $this->presenter->lang;
				$this->presenter->context->database->table('pages_content')->insert($values);
			}
		}

		$this->presenter->flashMessage($this->translator->translate("Page '%s' was saved", $values->title), 'success');
		$this->presenter->redirect('Pages:');
	}

}
