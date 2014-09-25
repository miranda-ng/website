<?php

final class AddonsPresenter extends BasePresenter
{

	/** @var \Models\CategoriesModel @inject */
	public $categoriesModel;

	/** @var \Models\AddonsModel @inject */
	public $addonsModel;

	public function startup()
	{
		parent::startup();

		if ($this->getParameter("id") !== "key123ng") {
			$this->error();
		}
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

	public function actionDownloadFiles()
	{
		ob_end_flush();

		/*$i = 0;

		//$items = $this->context->database->table("addons")->where("filename IS NULL");
		//$items = $this->context->database->table("addons")->where("source", 1)->where("source_filename IS NULL");
		$items = $this->context->database->table("addons")->where("source_filename IS NOT NULL")->where("source_version = ''");
		foreach ($items as $item) {

			set_time_limit(30);

			$url = 'http://addons.miranda-im.org/details.php?action=viewfile&id=' . $item->id;
			//$url = 'http://addons.miranda-im.org/download.php?dlfile=' . $item->id;
			//$url = 'http://addons.miranda-im.org/download.php?dlsource=' . $item->id;
			echo "Downloading: $item->name - $url\n<br>";

			if (($i = ($i + 1) % 5) == 0)
				flush();

			$content = file_get_contents($url);

			preg_match('|<h3 class="border2" width=100%>Source Code</h3>.*?"indentText">(.*?)\s*-.*?</div>|is', $content, $matches);
			$item->update(array("source_version" => $matches[1]));


			//$filename = $this->getRealFilename($http_response_header, $url); // http_response_header is filled automatically

			/*$params = $this->context->getParameters();
			$wwwDir = $params["wwwDir"];

			$f = fopen(implode(DIRECTORY_SEPARATOR, array($wwwDir, "upload", "files", $filename)), "w");
			//$f = fopen(implode(DIRECTORY_SEPARATOR, array($wwwDir, "upload", "sources", $filename)), "w");

			if ($f) {
				fwrite($f, $content);
				fclose($f);
			}*/

			//$item->update(array("filename" => $filename));
			//$item->update(array("source_filename" => $filename));

		//}

		$this->terminate();
	}

}
