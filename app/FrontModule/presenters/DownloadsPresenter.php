<?php

namespace FrontModule;

final class DownloadsPresenter extends BasePresenter
{
	/** @var \Models\PagesModel @inject */
	public $pagesModel;

	/*public function startup() {
		parent::startup();
		$httpResponse = $this->context->httpResponse;
		$httpResponse->setCode(Nette\Http\IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect('https://wiki.miranda-ng.org/index.php?title=Download');
		exit;
	}*/

	function renderDefault() {
		$this->template->page = $this->pagesModel->get(3);
	}
}
