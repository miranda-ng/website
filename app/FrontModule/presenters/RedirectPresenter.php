<?php

use Nette\Http\IResponse;

namespace FrontModule;

final class RedirectPresenter extends BasePresenter
{
	public function actionPlugin($id) {
		$url = Macros::getWikiLink('Plugin:' . $id, $this->lang);

		$httpResponse = $this->context->httpResponse;
		$httpResponse->setCode(IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect($url);
		exit;
	}
}
