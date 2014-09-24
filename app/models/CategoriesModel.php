<?php

namespace Models;

/**
 * @table categories
 */
final class CategoriesModel extends BaseModel {

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

	public function findCategories($published = TRUE) {
		$res = $this->getTable();

		if ($published) {
			$res->where("hidden", 0);
		}

		return $res;
	}

	public function getCategoriesArray($published = TRUE) {
		$categories = $this->findCategories($published)->order("name");

		$cats = array();
		foreach ($categories as $cat) {
			$cats[$cat->name] = $cat->id;
		}

		return $this->reshapeArray($cats);
	}

	public function getCategoriesCountsArray($published = TRUE) {
		$ids = $this->findCategories($published)->fetchPairs(NULL, "id");

		$counts = $this->database->table("addons")->select("categories_id, count(*) AS cnt")->where("categories_id", $ids)->group("categories_id")->fetchPairs("categories_id", "cnt");
		return $counts;
	}

}
