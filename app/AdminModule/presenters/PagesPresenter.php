<?php

namespace AdminModule;

class PagesPresenter extends SecuredPresenter
{
	protected $title = 'Pages';
	
	public function renderDefault() {
		$this->template->pages = $this->context->database->table('pages');
	}
	
	public function renderEdit($id) {
		$values = $this->context->database->table('pages')->get($id);
		if (!$values) {
			$this->flashMessage("News with this id doesn't exists.", "error");
			$this->redirect("default");
		}
		$this['pagesForm']->setValues($values);
	}
	
	public function createComponentPagesForm($name) {
		$form = new Forms\PagesForm($this, $name);
		return $form;
	}

}