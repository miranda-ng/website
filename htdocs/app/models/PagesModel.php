<?php

namespace Models;

/**
 * @table pages
 */
final class PagesModel extends BaseModel
{

	public function findPages($published = TRUE) {
		$res = $this->getTable();

		if ($published) {
			$res->where("hidden", 0);
		}

		return $res;
	}

	public function findPage($id, $published = TRUE) {
		return $this->findPages($published)->wherePrimary($id);
	}

	// TODO: working with page contents

}
