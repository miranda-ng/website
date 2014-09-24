<?php

final class AddonsPresenter extends BasePresenter
{

	/** @var \Rawbyer\Search @inject */
	public $search;

	/** @var \Models\CategoriesModel @inject */
	public $categoriesModel;

	/** @var \Models\AddonsModel @inject */
	public $addonsModel;

	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->categories = $this->categoriesModel->getCategoriesArray();
		$this->template->categoriesCounts = $this->categoriesModel->getCategoriesCountsArray();
	}

	public function renderDefault()
	{

	}

	public function renderCategory($id)
	{
		$category = $this->categoriesModel->findCategories()->where("id", $id)->fetch();
		if (!$category) {
			$this->error($this->translator->translate("Item was not found."));
		}

		$addons = $this->addonsModel->findAddonsByCategory($id)->select("*, COALESCE(updated, added) AS sortdate")->order("sortdate DESC");

		$vp = $this["vp"];
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $addons->count('id');
		$addons->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->page = $paginator->page;
		$this->template->pageCount = $paginator->pageCount;
		$this->template->itemCount = $paginator->itemCount;

		$this->template->addons = $addons;

		$names = explode("/", $category->name);
		$this->template->categoryName = array_pop($names);
		$this->template->activeCategoryId = $id;
	}

	public function renderSearch($id)
	{
		$params = $this->search->parseSearch($id);
		$this->template->search = $id;

		if (empty($params)) {
			$this->template->invalid = true;
		} else {
			$regexp = $this->search->prepareSearch($params, "/");
			$addons = $this->addonsModel->findAddons()
						->select("*, COALESCE(updated, added) AS sortdate")
						->where("name REGEXP ? OR description REGEXP ?", $regexp, $regexp)
						->order("sortdate DESC");

			$vp = $this["vp"];
			$paginator = $vp->getPaginator();
			$paginator->itemCount = $addons->count('id');
			$addons->limit($paginator->itemsPerPage, $paginator->offset);

			$this->template->highlight = $id;
			$this->template->page = $paginator->page;
			$this->template->pageCount = $paginator->pageCount;
			$this->template->itemCount = $paginator->itemCount;

			$this->template->addons = $addons;
		}
	}

	public function renderDetail($id)
	{
		$item = $this->addonsModel->findAddons()->where("id", $id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
		$this->template->activeCategoryId = $item->categories_id;
	}

	public function get_real_filename($headers,$url)
	{
		foreach($headers as $header)
		{
			if (strpos(strtolower($header),'content-disposition') !== false)
			{
				$tmp_name = explode('=', $header);
				if ($tmp_name[1]) return trim($tmp_name[1],'";\'');
			}
		}

		$stripped_url = preg_replace('|\\?.*|', '', $url);
		return basename($stripped_url);
	}

	public function actionDownloadFiles()
	{
		ob_end_flush();

		$i = 0;

		//$items = $this->context->database->table("addons")->where("filename IS NULL");
		//$items = $this->context->database->table("addons")->where("source", 1)->where("source_filename IS NULL");
		$items = $this->context->database->table("addons")->where("source_filename IS NOT NULL")->where("source_version = ''");
		foreach ($items as $item) {

			set_time_limit(30);

			$url = 'http://addons.miranda-im.org/details.php?action=viewfile&id=' . $item->id;
			//$url = 'http://addons.miranda-im.org/download.php?dlfile=' . $item->id;
			//$url = 'http://addons.miranda-im.org/download.php?dlsource=' . $item->id;
			echo "Downloading: $item->name - $url\n<br>";

			if (($i = ($i + 1) % 5) == 0)
				flush();

			$content = file_get_contents($url);

			preg_match('|<h3 class="border2" width=100%>Source Code</h3>.*?"indentText">(.*?)\s*-.*?</div>|is', $content, $matches);
			$item->update(array("source_version" => $matches[1]));


			//$filename = $this->get_real_filename($http_response_header, $url); // http_response_header is filled automatically

			/*$params = $this->context->getParameters();
			$wwwDir = $params["wwwDir"];

			$f = fopen(implode(DIRECTORY_SEPARATOR, array($wwwDir, "upload", "files", $filename)), "w");
			//$f = fopen(implode(DIRECTORY_SEPARATOR, array($wwwDir, "upload", "sources", $filename)), "w");

			if ($f) {
				fwrite($f, $content);
				fclose($f);
			}*/

			//$item->update(array("filename" => $filename));
			//$item->update(array("source_filename" => $filename));

		}

		$this->terminate();
	}

	public function createComponentVp($name) {
		$vp = new Components\VisualPaginator($this, $name, $this->translator, $this->texy);
		$vp->getPaginator()->itemsPerPage = 20;
		return $vp;
	}

	public function createComponentSearchForm($name) {
		$form = new \Nette\Application\UI\Form($this, $name);

		$form->getElementPrototype()->class = "search";

		$form->addText("s", "Search query")
				->setAttribute("placeholder", "Search...")
				->setRequired()
				->setDefaultValue($this->action == "search" ? $this->getParameter("id") : "")
				->addRule($form::MIN_LENGTH, NULL, 2);

		$form->addSubmit("submit", "Search");

		$form->onSuccess[] = function($form) {
			$values = $form->getValues();
			$values["s"] = str_replace('\\', '', $values["s"]);
			$values["s"] = str_replace('/', '', $values["s"]);

			$this->redirect('search', array("id" => ($values["s"])));
		};
	}

	public function highlight($text, $word, $truncate = NULL) {
		$text = strip_tags($text);

		if (!$word) {
			if ($truncate !== NULL)
				return \Nette\Utils\Strings::truncate($text, $truncate);
			else
				return $text;
		}

		return $this->search->highlight($text, $word, $truncate);
	}

}
