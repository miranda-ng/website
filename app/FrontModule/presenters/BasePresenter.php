<?php

namespace FrontModule;

use Nette\Utils\Strings;

abstract class BasePresenter extends \BasePresenter
{
	/** @var \Models\NewsModel @inject */
	public $newsModel;

    public function beforeRender()
    {
        parent::beforeRender();

		$this->template->news_panel = $this->newsModel->findNews()->where("important", 0)->order("date DESC")->limit(3);
		$this->template->important_news_panel = $this->newsModel->findNews()->where("important", 1)->order("date DESC")->limit(3);

		$wikiLink = $this->languagesModel->getWikiLink($this->lang);
		if (Strings::startsWith($wikiLink, "http://"))
			$wikiLink = substr($wikiLink, 5);
		if (Strings::startsWith($wikiLink, "https://"))
			$wikiLink = substr($wikiLink, 6);

		$this->template->menu = array(
			"Home:" => $this->translator->translate("Home"),
			"News:" => $this->translator->translate("News"),
			"Downloads:" => $this->translator->translate("Downloads"),
			"Addons:" => $this->translator->translate("Addons"),
			"VersionInfo:" => $this->translator->translate("Version Info"),
			"Development:" => $this->translator->translate("Development"),
			$wikiLink => $this->translator->translate("Wiki"),
			"//forum.miranda-ng.org/" => $this->translator->translate("Forum"),
		);

		if ($this->user->isLoggedIn()) {
			$this->template->menu[":Admin:Home:"] = $this->translator->translate("Admin");
		}

		$this->template->layout = "@html.latte";
    }

}
