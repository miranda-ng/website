<?php

namespace FrontModule;

use Components\VisualPaginator;

final class NewsPresenter extends BasePresenter
{
	/** @var \Models\NewsModel @inject */
	public $newsModel;

	public function renderDefault($important = 0)
	{
		$news = $this->template->news = $this->newsModel->findNews()->where("important", $important)->order("date DESC");

		$vp = $this["vp"];
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $news->count();
		$news->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->page = $paginator->page;
		$this->template->pageCount = $paginator->pageCount;

		if ($important) {
			$this->setView("important");
		}
	}

	public function renderShow($link)
	{
		$item = $this->context->database->table("news")/*->where("id", $id)*/->where("link", $link)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
	}

	public function createComponentVp($name) {
		$vp = new VisualPaginator($this, $name, $this->translator, $this->texy);
		$vp->getPaginator()->itemsPerPage = 6;
		return $vp;
	}

}
