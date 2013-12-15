<?php

final class DownloadsPresenter extends BasePresenter
{
	/*public function startup() {
		parent::startup();
		$httpResponse = $this->context->httpResponse;
		$httpResponse->setCode(Nette\Http\IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect('http://wiki.miranda-ng.org/index.php?title=Download');
		exit;
	}*/
	
	function renderDefault() {
		$this->template->page = $this->context->database->table("pages")->get(3);
	}
}
