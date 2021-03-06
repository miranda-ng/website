<?php

namespace FrontModule;

use Components\VisualPaginator;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

final class AddonsPresenter extends BasePresenter
{

	/** @var \Rawbyer\Search @inject */
	public $search;

	/** @var \Models\CategoriesModel @inject */
	public $categoriesModel;

	/** @var \Models\AddonsModel @inject */
	public $addonsModel;
	
	/** @persistent */
	public $magic = false;
	
	public function startup()
	{
		parent::startup();
		
		// In magic mode we alow showing all categories and all addons
		if ($this->magic) {
			$this->categoriesModel->magic = true;
			$this->addonsModel->magic = true;
		}
	}

	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->categories = $this->categoriesModel->getCategoriesArray();
		$this->template->categoriesCounts = $this->categoriesModel->getCategoriesCountsArray();
		$this->template->isAjax = $this->isAjax();
	}

	public function renderDefault()
	{
		$itemsCounts = $this->addonsModel->getMostDownloadedCountsArray(20, "-7 days");

		$order = "FIELD(id," . implode(",", array_keys($itemsCounts)) . ")";

		$this->template->items = $this->addonsModel->findAddons()->wherePrimary(array_keys($itemsCounts))->order($order);
		$this->template->itemsCounts = $itemsCounts;
	}

	public function renderCategory($id)
	{
		$category = $this->categoriesModel->findCategories()->wherePrimary($id)->fetch();
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
		$item = $this->addonsModel->findAddons()->wherePrimary($id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
		$this->template->activeCategoryId = $item->categories_id;

		if ($this->isAjax()) {
			$this->template->layout = "@colorbox.latte";
			$this->template->overlayClass = "sized";
		}
	}

	public function renderChangelog($id)
	{
		$item = $this->addonsModel->findAddons()->wherePrimary($id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
		$this->template->activeCategoryId = $item->categories_id;

		if ($this->isAjax()) {
			$this->template->layout = "@colorbox.latte";
			$this->template->overlayClass = "sized";
		}
	}

	public function renderImage($id)
	{
		$item = $this->addonsModel->findAddons()->wherePrimary($id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
		$this->template->activeCategoryId = $item->categories_id;

		if ($this->isAjax()) {
			$this->template->layout = "@colorbox.latte";
		}
	}

	public function createComponentVp($name) {
		$vp = new VisualPaginator($this, $name, $this->translator, $this->texy);
		$vp->getPaginator()->itemsPerPage = 20;
		return $vp;
	}

	public function createComponentSearchForm($name) {
		$form = new Form($this, $name);
		$form->setTranslator($this->translator);

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
				return Strings::truncate($text, $truncate);
			else
				return $text;
		}

		return $this->search->highlight($text, $word, $truncate);
	}

	public function actionDownload($id, $type = "file") {
		$item = $this->addonsModel->findAddons()->wherePrimary($id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}

		$wwwDir = $this->context->getParameters()["wwwDir"];

		switch ($type) {
			case "file":
			{
				$folder = "files";
				$name = $item->filename;
				break;
			}
			case "source":
			{
				$folder = "sources";
				$name = $item->source_filename;
				break;
			}
			default:
			{
				$this->error($this->translator->translate("Unknown type for download."));
			}
		}

		// Just to be sure we're pointing to safe file
		$name = preg_replace("#\\.\\.(\\\\|\\/)#", "", $name);
		$path = implode(DIRECTORY_SEPARATOR, [ $wwwDir, "upload", $folder, $item->id . "_" . $name ]);

		if (!$name || !is_file($path)) {
			$this->error($this->translator->translate("File doesn't exists."));
		}

		// Increment download count
		$this->addonsModel->incDownloadCount($item, $type);

		$this->sendResponse(new FileResponse($path, $name));
	}

}
