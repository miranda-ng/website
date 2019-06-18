<?php

namespace FrontModule;

final class OauthPresenter extends BasePresenter
{

	function renderVerification($code) {
		$this->template->code = $code;
	}

}
