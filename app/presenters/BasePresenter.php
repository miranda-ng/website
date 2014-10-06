<?php

use LiveTranslator\Translator;
use Models\LanguagesModel;
use Nette\Application\UI\Presenter;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Rules;
use Nette\Latte\Engine;
use Nette\Utils\Strings;
use WebLoader\Filter\VariablesFilter;

abstract class BasePresenter extends Presenter
{
	/** @persistent */
	public $lang;

	/** @var Nette\Localization\ITranslator @inject */
	public $translator;

	/** @var MyTexy @inject */
	public $texy;

	/** @var Models\LanguagesModel @inject */
	public $languagesModel;

    public function  startup() {
		parent::startup();

		$this->session->start();

		$langs = $this->languagesModel->getLanguages()->fetchPairs("code", "plural_forms");
		
		if (!$this->lang || !in_array($this->lang, array_keys($langs))) {
			$lang = $this->languagesModel->getDefaultLanguage();
			return $this->redirect("this", array("lang" => $lang));
		}

		$this->translator->setAvailableLanguages($langs);
		$this->translator->setPresenterLanguageParam("lang");

		$this->texy->setLang($this->lang);

		// Translate form's default error messages
		array_walk(Rules::$defaultMessages, function($message) {
			return @$this->translator->translate($message);
		});
	}

	public function getTransData($item, $table) {
		$data = null;
		$data_default = null;

		$res = $this->context->database->table("{$table}_content")->where("{$table}_id", $item->id)->where("lang", array($this->lang, LanguagesModel::LANG_DEFAULT))->order("lang = ? DESC", $this->lang)->limit(1)->fetch();
		return $res;

/*
		$item2 = clone $item;
		$res = $item->related("{$table}_content")->where("lang", $this->lang)->limit(1)->fetch();
		if (!$res) {
			$res = $item2->related("{$table}_content")->where("lang", \Models\LanguagesModel::LANG_DEFAULT)->limit(1)->fetch();
		}
		return $res;

		if ($data) {
			return $data;
		} elseif ($data_default) {
			return $data_default;
		}

		return NULL;*/
	}

	public function getNewsData($item)
	{
		return $this->getTransData($item, "news");
	}

	public function getPagesData($item)
	{
		return $this->getTransData($item, "pages");
	}

    public function beforeRender()
    {
        parent::beforeRender();

		if ($this->user->isLoggedIn() && !$this->isAjax())
			Debugger::enable(Debugger::DEVELOPMENT);

		$year = $this->context->parameters["year"];
		$this->template->copy = $year . (date("Y") > $year ? " - " . date("Y") : "");

		$this->template->sufix = $this->context->parameters["title"];
		$this->template->langs = $this->languagesModel->getLanguages();
		$this->template->lang = $this->lang;
    }

	/**
	 * Registers own template helpers.
	 * @param type $class
	 * @return type
	 */
	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
        $template->setTranslator($this->translator);
		return Macros::setupTemplate($template, $this->texy);
	}

	/**
	 * Registers own template macros.
	 * @param type $template
	 */
	public function templatePrepareFilters($template)
	{
		$template->registerFilter($latte = new Engine);
		Macros::setupMacros($latte->compiler);
	}

	protected function makeDescription($content) {
		if (!preg_match('#<p ?.*?>(.*?)</p>#ims', $content, $match))
			return NULL;

		$str = trim(strip_tags($match[1]));
		if (mb_strlen($str, "utf8") < 50)
			return NULL;

		return Strings::truncate($str, 255);
	}

	/**
	 * Texyla loader factory
	 * @return TexylaLoader
	 */
	protected function createComponentTexyla()
	{
		$baseUri = $this->context->httpRequest->url->baseUrl;
		$filter = new VariablesFilter(array(
			"baseUri" => $baseUri,
			"previewPath" => $this->link(":Texyla:preview"),
			"filesPath" => $this->link(":Texyla:listFiles"),
			"filesUploadPath" => $this->link(":Texyla:upload"),
			"filesMkDirPath" => $this->link(":Texyla:mkDir"),
			"filesRenamePath" => $this->link(":Texyla:rename"),
			"filesDeletePath" => $this->link(":Texyla:delete"),
		));

		$texyla = new TexylaLoader($filter, $baseUri."webtemp", $this->context->parameters["wwwDir"]);
		return $texyla;
	}

}
