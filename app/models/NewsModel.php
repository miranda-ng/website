<?php

namespace Models;

/**
 * @table news
 */
final class NewsModel extends BaseModel
{

	public function findNews($published = TRUE) {
		$res = $this->getTable();

		if ($published) {
			$res->where("hidden", 0);
		}

		return $res;
	}

	// TODO: working with page contents

}
