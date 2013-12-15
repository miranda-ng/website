<?php

final class DevelopmentPresenter extends BasePresenter
{
	function renderDefault() {
		$this->template->page = $this->context->database->table("pages")->get(2);
	}
}
