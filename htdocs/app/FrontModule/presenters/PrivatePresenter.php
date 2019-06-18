<?php

namespace FrontModule;

use InvalidArgumentException;
use Nette\Utils\Strings;

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


	/**
	 * Parse dictionary file
	 * @param string $file file path
	 * @param string
	 */
	private function parseFile($file, $identifier)
	{
		$f = @fopen($file, 'rb');
		if (@filesize($file) < 10) {
			throw new InvalidArgumentException("'$file' is not a gettext file.");
		}

		$endian = FALSE;
		$read = function ($bytes) use ($f, $endian) {
			$data = fread($f, 4 * $bytes);
			return $endian === FALSE ? unpack('V' . $bytes, $data) : unpack('N' . $bytes, $data);
		};

		$input = $read(1);
		if (Strings::lower(substr(dechex($input[1]), -8)) == '950412de') {
			$endian = FALSE;

		} elseif (Strings::lower(substr(dechex($input[1]), -8)) == 'de120495') {
			$endian = TRUE;

		} else {
			throw new InvalidArgumentException("'$file' is not a gettext file.");
		}

		$input = $read(1);

		$input = $read(1);
		$total = $input[1];

		$input = $read(1);
		$originalOffset = $input[1];

		$input = $read(1);
		$translationOffset = $input[1];

		fseek($f, $originalOffset);
		$orignalTmp = $read(2 * $total);
		fseek($f, $translationOffset);
		$translationTmp = $read(2 * $total);

		$dictionary = array();

		for ($i = 0; $i < $total; ++$i) {
			if ($orignalTmp[$i * 2 + 1] != 0) {
				fseek($f, $orignalTmp[$i * 2 + 2]);
				$original = @fread($f, $orignalTmp[$i * 2 + 1]);

			} else {
				$original = '';
			}

			if ($translationTmp[$i * 2 + 1] != 0) {
				fseek($f, $translationTmp[$i * 2 + 2]);
				$translation = fread($f, $translationTmp[$i * 2 + 1]);
				/*if ($original === '') {
					$this->metadata = $this->fileManager->parseMetadata($translation, $identifier, $this->metadata);
					continue;
				}*/

				$original = explode("\0", $original);
				$translation = explode("\0", $translation);

				$key = isset($original[0]) ? $original[0] : $original;
				$dictionary[$key]['original'] = $original;
				$dictionary[$key]['translation'] = $translation;
				$dictionary[$key]['file'] = $identifier;
			}
		}

		return $dictionary;
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

	private function updateTranslationsDb($lang)
	{
		$identifier = $lang;
		$file = $this->wwwDir . "/app/langs/$lang.front.mo";

		$trans = $this->parseFile($file, $identifier);

		$arr = array();

		foreach ($trans as $original => $data) {
			$translation = $data["translation"];

			if ($original)
				$arr[$original] = $translation[0];
		}

		//print_r($arr);


		foreach ($this->context->database->table("localization_text") as $item) {
			if (isset($arr[$item->text])) {
				if ($this->context->database->table("localization")
						->where("text_id", $item->id)
						->where("lang", $lang)
						->where("variant", 0)
						->fetch() != NULL) {
					// It is already in DB
					continue;
				}

				$this->context->database->table("localization")->insert([
					"text_id" => $item->id,
					"lang" => $lang,
					"variant" => 0,
					"translation" => $arr[$item->text],
				]);

				echo "Added translation: '{$item->text}' -> '{$arr[$item->text]}'<br>";
				flush();
			}
		}
	}

	public function actionLoadTranslations()
	{
		ob_end_flush();

		$items = ["be", "cs", "de", "en", "fr", "he", "pl", "ru"];

		foreach ($items as $lang) {
			$this->updateTranslationsDb($lang);
		}

		$this->terminate();
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
