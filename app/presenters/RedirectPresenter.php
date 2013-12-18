<?php

final class RedirectPresenter extends BasePresenter
{
	public function actionPlugin($id) {
		// Wiki has own language resolution right now, so we don't need to append lang
		$url = 'http://wiki.miranda-ng.org/index.php?title=Plugin:' . $id; //. '/' . $this->lang;

		$httpResponse = $this->context->httpResponse;
		$httpResponse->setCode(Nette\Http\IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect($url);
		exit;
	}
}
