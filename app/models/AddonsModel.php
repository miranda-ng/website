<?php

namespace Models;

/**
 * @table addons
 */
final class AddonsModel extends BaseModel {

	public function findAddons($published = TRUE) {
		$res = $this->getTable();

		if ($published) {
			$ids = $this->database->table("categories")->where("hidden", 0)->fetchPairs(NULL, "id");
			$res->where("categories_id", $ids);
		}

		return $res;
	}

	public function findAddonsByCategory($categoryId) {
		$res = $this->getTable();

		$res->where("categories_id", $categoryId);

		return $res;
	}



}
