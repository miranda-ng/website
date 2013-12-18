<?php

namespace AdminModule;

class NewsPresenter extends SecuredPresenter
{
	public function renderDefault() {
		$this->template->news = $this->context->database->table('news')->order('important DESC, date DESC');
	}

	public function renderEdit($id) {
		$news = $this->context->database->table('news')->get($id);
		if (!$news) {
			$this->flashMessage($this->translator->translate("News with this id doesn't exists."), "error");
			$this->redirect("default");
		}
		if ($this->lang == self::LANG_DEFAULT) {
			$this['newsForm']['basic']->setValues($news);
			$this['newsForm']['basic']['date']->value = $news["date"]->format("Y-m-d H:i:s");
		}

		$news_content = $this->getNewsData($news);
		$this['newsForm']['details']->setValues($news_content);
	}

	public function actionAdd() {
		if ($this->lang != self::LANG_DEFAULT) {
			$this->flashMessage($this->translator->translate("News could be added only when using main language"), "error");
			$this->redirect("default");
		}
	}

	public function renderAdd() {
		$this['newsForm']['submit']->caption = $this->translator->translate('Add');
	}

	public function actionDelete($id) {
		if ($this->lang != self::LANG_DEFAULT) {
			$this->flashMessage($this->translator->translate("News could be deleted only when using main language"), "error");
			$this->redirect("default");
		}

		try {
			if (!$this->context->database->table('news')->wherePrimary($id)->delete())
				$this->flashMessage($this->translator->translate('Error when deleting news'), 'error');
			else
				$this->flashMessage($this->translator->translate('Succesfully deleted'), 'success');
		} catch (Exception $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}

		$this->redirect('News:');
	}

	public function createComponentNewsForm($name) {
		$form = new Forms\NewsForm($this, $name, $this->translator);
		return $form;
	}

}