<?php

final class RssPresenter extends BasePresenter
{
	/** @var \Models\NewsModel @inject */
	public $newsModel;

	public function beforeRender() {
		parent::beforeRender();
		$this->session->start();

		if ($this->action == "default") {
			$this->redirect("news");
		}
	}

	public function renderNews()
	{
		$limit = 15;

		$this->template->translatedOnly = $this->getParameter("translated") == "force";

		$data = $this->newsModel->findNews()->order("date DESC")->limit($limit);
		$this->template->data = $data;
		$this->setView("default");
	}

}