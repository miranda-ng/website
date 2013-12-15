<?php

final class RedirectPresenter extends BasePresenter
{
	public function actionPlugin($id) {
		$httpResponse = $this->context->httpResponse;
		$httpResponse->setCode(Nette\Http\IResponse::S301_MOVED_PERMANENTLY);
		$httpResponse->redirect('http://wiki.miranda-ng.org/index.php?title=Plugin:' . $id . '/en');
		exit;
	}
}
