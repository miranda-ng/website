<?php

namespace AdminModule;

abstract class BasePresenter extends \BasePresenter
{
	public function startup() {
		parent::startup();
		$this->session->start();
		
		$this->translator->setNamespace('admin');
	}

	public function beforeRender() {
		parent::beforeRender();

		$this->template->sufix = $this->translator->translate('Administration') .  ' - Miranda NG';
		$this->template->menu = array(
			"Home:" => $this->translator->translate("Admin"),
			"Pages:" => $this->translator->translate("Pages"),
			"News:" => $this->translator->translate("News"),
			":Home:" => $this->translator->translate("Home"),
		);

		$this->template->original = $this->lang == \Models\LanguagesModel::LANG_DEFAULT;
	}

	/**
	 * Texyla loader factory
	 * @return TexylaLoader
	 */
	protected function createComponentTexyla()
	{
		$baseUri = $this->context->httpRequest->url->baseUrl;
		$params = array("lang" => \Models\LanguagesModel::LANG_DEFAULT);
		$filter = new \WebLoader\Filter\VariablesFilter(array(
			"baseUri" => $baseUri,
			"previewPath" => $this->link("Texyla:preview", $params),
			"filesPath" => $this->link("Texyla:listFiles", $params),
			"filesUploadPath" => $this->link("Texyla:upload", $params),
			"filesMkDirPath" => $this->link("Texyla:mkDir", $params),
			"filesRenamePath" => $this->link("Texyla:rename", $params),
			"filesDeletePath" => $this->link("Texyla:delete", $params),
		));

		$texyla = new \TexylaLoader($filter, $baseUri."webtemp", $this->context->parameters["wwwDir"]);
		return $texyla;
	}

}
