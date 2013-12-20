<?php

final class NewsPresenter extends BasePresenter
{
	public function renderDefault($important = 0)
	{
		$news = $this->template->news = $this->context->database->table("news")->where("important", $important)->order("date DESC");

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
			throw new \Nette\Application\BadRequestException($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
	}

	public function createComponentVp($name) {
		$vp = new Components\VisualPaginator($this, $name, $this->translator);
		$vp->getPaginator()->itemsPerPage = 6;
		return $vp;
	}

}
