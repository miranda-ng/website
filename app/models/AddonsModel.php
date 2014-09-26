<?php

namespace Models;

use DateInterval;
use DateTime;
use InvalidArgumentException;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;

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

	public function getMostDownloadedCountsArray($limit, $interval = NULL) {
		if ($interval) {
			$end = new DateTime();
			$start = clone $end;
			$start->add(DateInterval::createFromDateString($interval));

			return $this->getTable("_downloads")
					->select("addons_id AS id, SUM(downloads) AS cnt")
					->where("date >= ?", $start)
					->where("date <= ?", $end)
					->group("addons_id")
					->having("cnt > 0")
					->order("cnt DESC")
					->limit($limit)
					->fetchPairs("id", "cnt");
		}

		return $this->getTable()
				->select("id, downloads AS cnt")
				->where("downloads > 0")
				->order("downloads DESC")
				->fetchPairs("id", "cnt");
	}

	public function incDownloadCount(ActiveRow $item, $type = "file") {
		switch ($type) {
			case "file":
				$column = "downloads";
				break;
			case "source":
				$column = "source_downloads";
				break;
			default:
				throw new InvalidArgumentException("Unknown type '$type'.");
		}

		$item->update([
			$column => new SqlLiteral("downloads + 1"),
		]);

		$this->database->beginTransaction();
		{
			$download = $this->getTable("_downloads")->where("addons_id", $item->id)->where("date", new DateTime())->fetch();
			if ($download) {
				$download->update([
					$column => new SqlLiteral("$column + 1"),
				]);
			} else {
				$this->getTable("_downloads")->insert([
					"addons_id" => $item->id,
					"date" => new DateTime(),
					$column => 1,
				]);
			}
		}
		$this->database->commit();
	}

}
