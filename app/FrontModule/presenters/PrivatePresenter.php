<?php

namespace FrontModule;

final class PrivatePresenter extends BasePresenter
{

	/** @var \Models\CategoriesModel @inject */
	public $categoriesModel;

	/** @var \Models\AddonsModel @inject */
	public $addonsModel;

	/** @var string */
	private $wwwDir;

	public function startup()
	{
		parent::startup();

		if ($this->getParameter("id") !== "key123ng") {
			$this->error();
		}

		$this->wwwDir = $this->context->getParameters()["wwwDir"];
	}

	private function getRealFilename($headers, $url)
	{
		foreach($headers as $header)
		{
			if (strpos(strtolower($header),'content-disposition') !== false)
			{
				$tmp_name = explode('=', $header);
				if ($tmp_name[1]) return trim($tmp_name[1],'";\'');
			}
		}

		$stripped_url = preg_replace('|\\?.*|', '', $url);
		return basename($stripped_url);
	}

	public function actionCheckFiles()
	{
		ob_end_flush();

		/*$i = 0;
		$items = $this->context->database->table("addons");
		foreach ($items as $item) {

			set_time_limit(30);

			$filename = implode(DIRECTORY_SEPARATOR, [ $this->wwwDir, "upload", "files", $item->filename ]);

			if (!file_exists($filename)) {
				echo "Invalid: #$item->id - $item->filename<br>";
			}

			if (($i = ($i + 1) % 20) == 0)
				flush();
		}*/

		$this->terminate();
	}

	public function actionRenameFiles()
	{
		ob_end_flush();

		/*$i = 0;
		$items = $this->context->database->table("addons")->where("renamed", 0);
		foreach ($items as $item) {

			set_time_limit(30);

			$oldname = implode(DIRECTORY_SEPARATOR, [ $this->wwwDir, "upload", "files", $item->filename ]);
			$newname = implode(DIRECTORY_SEPARATOR, [ $this->wwwDir, "upload", "files", $item->id . "_" . $item->filename ]);

			if (@rename($oldname, $newname)) {
				echo "Renamed: #$item->id - $item->filename<br>";
				$item->update([
					"renamed" => 1,
				]);
			}

			if (($i = ($i + 1) % 20) == 0)
				flush();
		}*/

		$this->terminate();
	}

	public function actionDownloadFiles()
	{
		ob_end_flush();

		/*$i = 0;
		$type = "source"; // "file";

		//$items = $this->context->database->table("addons")->where("filename IS NULL");
		$items = $this->context->database->table("addons")->where("downloaded", 0)->where("source_filename IS NOT NULL");
		//$items = $this->context->database->table("addons")->where("downloaded", 0);

		foreach ($items as $item) {

			set_time_limit(30);

			//$url = 'http://addons.miranda-im.org/details.php?action=viewfile&id=' . $item->addons_id;
			if ($type == "source") {
				$url = 'http://addons.miranda-im.org/download.php?dlsource=' . $item->addons_id;
			} else {
				$url = 'http://addons.miranda-im.org/download.php?dlfile=' . $item->addons_id;
			}

			echo "Downloading $type: $item->name - $url\n<br>";

			if (($i = ($i + 1) % 10) == 0)
				flush();

			$content = file_get_contents($url);

			//preg_match('|<h3 class="border2" width=100%>Source Code</h3>.*?"indentText">(.*?)\s*-.*?</div>|is', $content, $matches);
			//$item->update(array("source_version" => $matches[1]));

			$filename = $this->getRealFilename($http_response_header, $url); // http_response_header is filled automatically

			$path = implode(DIRECTORY_SEPARATOR, array($this->wwwDir, "upload", $type == "source" ? "sources" : "files", $item->id . "_" . $filename));

			$f = fopen($path, "w");
			if ($f) {
				fwrite($f, $content);
				fclose($f);
			}

			$item->update([
				"downloaded" => 1,
			]);
		}*/

		$this->terminate();
	}

	public function actionDownloadChangelogs()
	{
		ob_end_flush();

		$i = 0;
		$items = $this->context->database->table("addons")->where("changelog IS NULL");//->where("downloaded", 0);
		foreach ($items as $item) {
			set_time_limit(30);

			$url = 'http://addons.miranda-im.org/details.php?action=viewlog&id=' . $item->addons_id;

			echo "Downloading $item->name - $url\n<br>";

			if (($i = ($i + 1) % 10) == 0)
				flush();

			$content = file_get_contents($url);

			preg_match('|View file information</a>.*?<div align="left">(.*?)<hr />|is', $content, $matches);

			if (!isset($matches[1])) {
				continue;
			}

			$changelog = $matches[1];
			$item->update([
				"changelog" => $changelog,
				//"downloaded" => 1,
			]);
		}

		$this->terminate();
	}

}
