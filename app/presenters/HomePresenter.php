<?php

final class HomePresenter extends BasePresenter
{
	function renderDefault()
	{
		$this->template->page = $this->context->database->table("pages")->get(1);
		$this->template->sufix .= " - " . $this->translator->translate("Next Generation of Miranda IM");
	}
}
