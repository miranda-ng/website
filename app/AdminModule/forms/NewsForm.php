<?php

namespace AdminModule\Forms;

use DateTime;
use Models\LanguagesModel;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;

class NewsForm extends Form
{
	/** @var ITranslator */
    public $translator;

	public function __construct($parent = null, $name = null, $translator) {
		parent::__construct($parent, $name);
		$this->translator = $translator;

		$this->setTranslator($this->translator);

		$this->addProtection('Timeout for security token. Submit form again.');

		if ($this->presenter->lang == LanguagesModel::LANG_DEFAULT) {
			$basic = $this->addContainer("basic");

			$basic->addText('link', 'URL link:', 70, 255)
				->setRequired('Enter url link');

			$basic->addText('date', 'Date:', 20, 20)
				->setRequired('Enter date')
				->setAttribute('class','date')
				->setDefaultValue(date('Y-m-d H:i:s'));

			$basic->addCheckbox('important', 'This is important announcement', 20, 20);
		}

		$details = $this->addContainer("details");

		$details->addText('title', 'Title:', 70, 255)
			->setRequired('Enter title');

		$details->addTextArea('description', 'Description:', 40, 5)
			->setAttribute('class', 'texyla');

		$details->addTextArea('content', 'Content:', 40, 20)
			->setAttribute('class', 'texyla');

		$this->addSubmit('submit', 'Save');

		$this->onSuccess[] = $this->formSubmitted;
	}

	public function formSubmitted($form) {
		$values = $form->getValues();

		$id = $this->presenter->getParameter('id');

		if ($this->presenter->lang == LanguagesModel::LANG_DEFAULT) {
			$values->basic->date = new DateTime($values->basic->date);

			if (!$id) {
				$res = $this->presenter->context->database->table('news')->insert($values->basic);

				$values->details->author = $this->presenter->user->id;
				$values->details->news_id = $this->presenter->context->database->connection->getInsertId();
				$values->details->lang = $this->presenter->lang;
				$res = $this->presenter->context->database->table('news_content')->insert($values->details);
			} else {
				$res = $this->presenter->context->database->table('news')->wherePrimary($id)->update($values->basic);

				$res = $this->presenter->context->database->table('news_content')->where("news_id", $id)->where("lang", $this->presenter->lang)->fetch();
				if ($res) {
					$res->update($values->details);
				} else {
					$values->details->author = $this->presenter->user->id;
					$values->details->news_id = $id;
					$values->details->lang = $this->presenter->lang;
					$this->presenter->context->database->table('news_content')->insert($values->details);
				}

			}

		} else {

			if (!$id) {
				// nothing, we won't add news from non-default language
			} else {

				$res = $this->presenter->context->database->table('news_content')->where("news_id", $id)->where("lang", $this->presenter->lang)->fetch();
				if ($res) {
					$res->update($values->details);
				} else {
					$values->details->author = $this->presenter->user->id;
					$values->details->news_id = $id;
					$values->details->lang = $this->presenter->lang;
					$this->presenter->context->database->table('news_content')->insert($values->details);
				}

			}

		}

		$this->presenter->flashMessage($this->translator->translate("News '%s' was saved", $values->details->title), 'success');
		$this->presenter->redirect('News:');
	}

}
