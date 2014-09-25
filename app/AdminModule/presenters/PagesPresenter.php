<?php

namespace AdminModule;

class PagesPresenter extends SecuredPresenter
{
	/** @var \Models\PagesModel @inject */
	public $pagesModel;

	public function renderDefault() {
		$this->template->pages = $this->pagesModel->findPages(FALSE);
	}

	public function renderEdit($id) {
		$page = $this->pagesModel->get($id);
		if (!$page) {
			$this->flashMessage($this->translator->translate("Page with this id doesn't exists."), "error");
			$this->redirect("default");
		}
		$page_content = $this->getPagesData($page);
		$this['pagesForm']->setValues($page_content);
	}

	public function createComponentPagesForm($name) {
		$form = new Forms\PagesForm($this, $name, $this->translator);
		return $form;
	}

}