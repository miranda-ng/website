<?php

final class AddonsPresenter extends BasePresenter
{

	/**
	 * Make array structurized by exploding keys and create new subarrays when needed.
	 *
	 * @param array $data
	 * @return array
	 */
	private function reshapeArray($data) {
		$arr = array();

		foreach ($data as $setting => $value) {
			$name_parts = explode("/", $setting);

			$place = &$arr;
			for ($i = 0; $i < count($name_parts); $i++) {
				$name_part = $name_parts[$i];

				if ($i !== count($name_parts) - 1) {
					// This is not last item, se must go deeper
					if (!isset($place[$name_part])) {
						$place[$name_part] = array();
					}

					$place = &$place[$name_part];
					continue;
				}

				// Last item, here we finally put the value
				$place[$name_part] = $value;
			}
		}

		return $arr;
	}

	public function beforeRender()
	{
		parent::beforeRender();

		$categories = $this->context->database->table("categories");

		$cats = array();
		foreach ($categories as $cat) {
			$cats[$cat->name] = $cat->id;
		}

		$this->template->categories = $this->reshapeArray($cats);
	}

	public function renderDefault()
	{

	}

	public function renderCategory($id)
	{
		$category = $this->context->database->table("categories")->where("id", $id)->fetch();
		if (!$category) {
			$this->error($this->translator->translate("Item was not found."));
		}

		$addons = $this->context->database->table("addons")->select("*, COALESCE(updated, added) AS sortdate")->where("categories_id", $id)->order("sortdate DESC");

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
	}

	public function renderDetail($id)
	{
		$item = $this->context->database->table("addons")->where("id", $id)->fetch();
		if (!$item) {
			$this->error($this->translator->translate("Item was not found."));
		}
		$this->template->item = $item;
	}

	public function createComponentVp($name) {
		$vp = new Components\VisualPaginator($this, $name, $this->translator, $this->texy);
		$vp->getPaginator()->itemsPerPage = 20;
		return $vp;
	}

}
