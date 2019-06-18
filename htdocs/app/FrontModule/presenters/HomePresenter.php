<?php

namespace FrontModule;

final class HomePresenter extends BasePresenter
{
	/** @var \Models\PagesModel @inject */
	public $pagesModel;

	function renderDefault()
	{
		$this->template->page = $this->pagesModel->get(1);
		$this->template->sufix .= " - " . $this->translator->translate("Next Generation of Miranda IM");
	}
}
