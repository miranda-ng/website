<?php

final class NewsPresenter extends BasePresenter
{
	public function renderDefault()
	{
		$news = $this->template->news = $this->context->database->table("news")->order("date DESC");

		$vp = $this["vp"];
		$paginator = $vp->getPaginator();
		$paginator->itemCount = $news->count();
		$news->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->page = $paginator->page;
		$this->template->pageCount = $paginator->pageCount;
	}

	public function renderShow($link)
	{
		$item = $this->context->database->table("news")/*->where("id", $id)*/->where("link", $link)->fetch();
		if (!$item) {
			throw new \Nette\Application\BadRequestException($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
	}

	public function createComponentVp() {
		$vp = new Components\VisualPaginator();
		$vp->getPaginator()->itemsPerPage = 6;
		return $vp;
	}

}
