<?php

namespace AdminModule;

use Models\LanguagesModel;

abstract class BasePresenter extends \BasePresenter
{

	public function beforeRender() {
		parent::beforeRender();

		$this->template->sufix = $this->translator->translate('Administration') .  ' - Miranda NG';
		$this->template->menu = array(
			"Home:" => $this->translator->translate("Admin"),
			"Pages:" => $this->translator->translate("Pages"),
			"News:" => $this->translator->translate("News"),
			":Front:Home:" => $this->translator->translate("Home"),
		);

		$this->template->original = $this->lang == LanguagesModel::LANG_DEFAULT;
	}

}
