<?php

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Rules;
use Nette\Latte\Engine;
use Nette\Utils\Strings;
use WebLoader\Filter\VariablesFilter;

abstract class BasePresenter extends Presenter
{
	/** @persistent */
	public $lang;

	/** @var \GettextTranslator\Gettext @inject */
	public $translator;

	/** @var MyTexy @inject */
	public $texy;

	/** @var \Models\LanguagesModel @inject */
	public $languagesModel;
	
	/** @var \Models\NewsModel @inject */
	public $newsModel;

    public function  startup() {
		parent::startup();

		if (!$this->lang) {
			$lang = $this->languagesModel->getDefaultLanguage();
			return $this->redirect("this", array("lang" => $lang));
		}

		$this->translator->setLang($this->lang);
		$this->texy->setLang($this->lang);

		// Translate form's default error messages
		array_walk(Rules::$defaultMessages, function($message) {
			return $this->translator->translate($message);
		});
	}

	public function getTransData($item, $table) {
		$data = null;
		$data_default = null;

		$res = $this->context->database->table("{$table}_content")->where("{$table}_id", $item->id)->where("lang", array($this->lang, \Models\LanguagesModel::LANG_DEFAULT))->order("lang = ? DESC", $this->lang)->limit(1)->fetch();
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

		$this->session->start();

		if ($this->user->isLoggedIn() && !$this->isAjax())
			Debugger::enable(Debugger::DEVELOPMENT);

		$year = $this->context->parameters["year"];
		$this->template->copy = $year . (date("Y") > $year ? " - " . date("Y") : "");

		$this->template->sufix = $this->context->parameters["title"];

		$this->template->news_panel = $this->newsModel->findNews()->where("important", 0)->order("date DESC")->limit(3);
		$this->template->important_news_panel = $this->newsModel->findNews()->where("important", 1)->order("date DESC")->limit(3);
		$this->template->langs = $this->languagesModel->getLanguages();

		$wikiLink = "//wiki.miranda-ng.org";
		// TODO: load this wiki link from database
		switch ($this->lang) {
			case "by":
				$wikiLink = MAcros::GetWikiLink("%D0%93%D0%B0%D0%BB%D0%BE%D1%9E%D0%BD%D0%B0%D1%8F_%D1%81%D1%82%D0%B0%D1%80%D0%BE%D0%BD%D0%BA%D0%B0");
				break;
			case "cs":
				$wikiLink = MAcros::GetWikiLink("Hlavn%C3%AD_strana");
				break;
			case "de":
				$wikiLink = MAcros::GetWikiLink("Hauptseite");
				break;
			case "en":
				$wikiLink = MAcros::GetWikiLink("Main_Page");
				break;
			case "fr":
				$wikiLink = MAcros::GetWikiLink("Page_principale");
				break;
			case "pl":
				$wikiLink = MAcros::GetWikiLink("Strona_g%C5%82%C3%B3wna");
				break;
			case "ru":
				$wikiLink = MAcros::GetWikiLink("%D0%97%D0%B0%D0%B3%D0%BB%D0%B0%D0%B2%D0%BD%D0%B0%D1%8F_%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B0");
				break;
			case "sk":
				$wikiLink = MAcros::GetWikiLink("Hlavn%C3%A1_str%C3%A1nka");
				break;
		}
		if (Strings::startsWith($wikiLink, "http://"))
			$wikiLink = substr($wikiLink, 5);

		$this->template->lang = $this->lang;

		$this->template->menu = array(
			"Home:" => $this->translator->translate("Home"),
			"News:" => $this->translator->translate("News"),
			"Downloads:" => $this->translator->translate("Downloads"),
			"Addons:" => $this->translator->translate("Addons"),
			"Development:" => $this->translator->translate("Development"),
			$wikiLink => $this->translator->translate("Wiki"),
			"//forum.miranda-ng.org/" => $this->translator->translate("Forum"),
		);

		if ($this->user->isLoggedIn()) {
			$this->template->menu["Admin:Home:"] = $this->translator->translate("Admin");
		}

		/*$quotes = array();
		$quotes[] = "„Do you know that <strong>Miranda NG</strong><br>is smaller, faster and easier?“";
		//$quotes[] = "„Sun takes no prisoners!“";
		$quotes[] = "„Miranda NG is better than sex!“<br><b>Satisfied user</b>";
		$quotes[] = "„I don't always use Instant messengers...<br>But when I do, I use <strong>Miranda NG</strong>!";

		$this->template->quote = $quotes[array_rand($quotes)];*/
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
			"previewPath" => $this->link("Texyla:preview"),
			"filesPath" => $this->link("Texyla:listFiles"),
			"filesUploadPath" => $this->link("Texyla:upload"),
			"filesMkDirPath" => $this->link("Texyla:mkDir"),
			"filesRenamePath" => $this->link("Texyla:rename"),
			"filesDeletePath" => $this->link("Texyla:delete"),
		));

		$texyla = new TexylaLoader($filter, $baseUri."webtemp", $this->context->parameters["wwwDir"]);
		return $texyla;
	}

}
