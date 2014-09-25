<?php

final class DevelopmentPresenter extends BasePresenter
{
	/** @var \Models\PagesModel @inject */
	public $pagesModel;

	function renderDefault() {
		$this->template->page = $this->pagesModel->get(2);
	}
}
