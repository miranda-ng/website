<?php

namespace AdminModule;

class NewsPresenter extends SecuredPresenter
{
	protected $title = 'News';
	
	public function renderDefault() {
		$this->template->news = $this->context->database->table('news')->order('date DESC');
	}
	
	public function renderEdit($id) {
		$values = $this->context->database->table('news')->get($id);
		if (!$values) {
			$this->flashMessage("News with this id doesn't exists.", "error");
			$this->redirect("default");
		}
		$this['newsForm']->setValues($values);
		$this['newsForm']['date']->value = $values["date"]->format("Y-m-d H:i:s");
	}
	
	public function renderAdd() {
		$this['newsForm']['submit']->caption = 'Add';
	}
	
	public function actionDelete($id) {
		try {
			if (!$this->context->database->table('news')->find($id)->delete())
				$this->flashMessage('Error when deleting news', 'error');	
			else
				$this->flashMessage('Succesfully deleted', 'success');
		} catch (Exception $e) {
			$this->flashMessage($e->getMessage(), 'error');
		}
		
		$this->redirect('News:');
	}
	
	public function createComponentNewsForm($name) {
		$form = new Forms\NewsForm($this, $name);
		return $form;
	}

}