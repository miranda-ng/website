<?php

namespace FrontModule;

use Nette\Http\IResponse;

final class RedirectPresenter extends BasePresenter
{
	public function actionPlugin($id) {
		$url = \Macros::getWikiLink('Plugin:' . $id, $this->lang);

		$httpResponse = $this->context->httpResponse;
		$httpResponse->setHeader('Link', '<' . $url . '>; rel="alternate"; hreflang="x-default"');
		$httpResponse->setCode(IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect($url);
		exit;
	}
}
