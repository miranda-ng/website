<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $lang;

    /** @var GettextTranslator\Gettext @inject */
    public $translator;

    public function  startup() {
        parent::startup();

    }

    public function beforeRender()
    {
        parent::beforeRender();

		$year = $this->context->parameters["year"];
		$this->template->copy = $year . (date("Y") > $year ? " - " . date("Y") : "");

		$this->template->sufix = $this->context->parameters["title"];

		$this->template->news_panel = $this->context->database->table("news")->order("date DESC")->limit(3);

		$this->template->langs = $langs = array(
			(object)array(
				"id" => "en",
				"icon" => "us",
				"title" => "English",
			),
			(object)array(
				"id" => "ru",
				"icon" => "ru",
				"title" => "Russian",
			),
			(object)array(
				"id" => "cs",
				"icon" => "cz",
				"title" => "Čeština",
			),
			(object)array(
				"id" => "de",
				"icon" => "de",
				"title" => "German",
			),
			(object)array(
				"id" => "pl",
				"icon" => "pl",
				"title" => "Polish",
			),
		);

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

		return \Macros::setupTemplate($template);
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
