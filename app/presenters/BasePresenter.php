<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang;

	/** @var GettextTranslator\Gettext @inject */
	public $translator;

	const LANG_DEFAULT = "en";

    public function  startup() {
		parent::startup();

		if (!$this->lang) {
			$langs = $this->context->database->table("languages")->order("code = ? DESC, code", self::LANG_DEFAULT)->fetchPairs("code", "code");
			$lang = $this->context->httpRequest->detectLanguage(array_values($langs)) ?: self::LANG_DEFAULT;
			return $this->redirect("this", array("lang" => $lang));
		}
	}

	public function getTransData($item, $table) {
		$data = null;
		$data_default = null;

		$res = $this->context->database->table("{$table}_content")->where("{$table}_id", $item->id)->where("lang", array($this->lang, self::LANG_DEFAULT))->order("lang = ? DESC", $this->lang)->limit(1)->fetch();
		return $res;

/*
		$item2 = clone $item;
		$res = $item->related("{$table}_content")->where("lang", $this->lang)->limit(1)->fetch();
		if (!$res) {
			$res = $item2->related("{$table}_content")->where("lang", self::LANG_DEFAULT)->limit(1)->fetch();
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
			\Nette\Diagnostics\Debugger::enable(\Nette\Diagnostics\Debugger::DEVELOPMENT);

		$year = $this->context->parameters["year"];
		$this->template->copy = $year . (date("Y") > $year ? " - " . date("Y") : "");

		$this->template->sufix = $this->context->parameters["title"];

		$this->template->news_panel = $this->context->database->table("news")->order("date DESC")->limit(3);
		$this->template->langs = $this->context->database->table("languages")->order("code = ? DESC, code", self::LANG_DEFAULT);

		$this->template->menu = array(
			"Home:" => $this->translator->translate("Home"),
			"News:" => $this->translator->translate("News"),
			//"About:" => $this->translator->translate("About"),
			"Downloads:" => $this->translator->translate("Downloads"),
			"Development:" => $this->translator->translate("Development"),
			//"//wiki.miranda-ng.org/index.php?title=Download" => "Downloads",
			"//wiki.miranda-ng.org" => $this->translator->translate("Wiki"),
			//"Forums:" => "Forums",
			"//forum.miranda-ng.org/" => $this->translator->translate("Forum"),
		);

		if ($this->user->isLoggedIn()) {
			$this->template->menu["Admin:Home:"] = $this->translator->translate("Admin");
		}

		$quotes = array();
		$quotes[] = "„Do you know that <strong>Miranda NG</strong><br>is smaller, faster and easier?“";
		//$quotes[] = "„Sun takes no prisoners!“";
		$quotes[] = "„Miranda NG is better than sex!“<br><b>Satisfied user</b>";
		$quotes[] = "„I don't always use Instant messengers...<br>But when I do, I use <strong>Miranda NG</strong>!";

		$this->template->quote = $quotes[array_rand($quotes)];
    }

	/**
	 * Registers own template helpers.
	 * @param type $class
	 * @return type
	 */
	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);

		// if not set, the default language will be used
        if (!isset($this->lang)) {
            $this->lang = $this->translator->getLang();
        } else {
            $this->translator->setLang($this->lang);
        }

        $template->setTranslator($this->translator);

		return \Macros::setupTemplate($template, $this->context->parameters["wwwDir"]);
	}

	/**
	 * Registers own template macros.
	 * @param type $template
	 */
	public function templatePrepareFilters($template)
	{
		$template->registerFilter($latte = new \Nette\Latte\Engine);
		\Macros::setupMacros($latte->compiler);
	}

	protected function makeDescription($content) {
		if (!preg_match('#<p ?.*?>(.*?)</p>#ims', $content, $match))
			return NULL;

		$str = trim(strip_tags($match[1]));
		if (mb_strlen($str, "utf8") < 50)
			return NULL;

		return \Nette\Utils\Strings::truncate($str, 255);
	}

	/**
	 * Texyla loader factory
	 * @return TexylaLoader
	 */
	protected function createComponentTexyla()
	{
		$baseUri = $this->context->httpRequest->url->baseUrl;
		$filter = new WebLoader\Filter\VariablesFilter(array(
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
