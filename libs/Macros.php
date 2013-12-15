<?php

class Macros
{
	
	public static function setupMacros($compiler) {
		
		$set = new \Nette\Latte\Macros\MacroSet($compiler);
				
		$set->addMacro('icon','echo ' . __NAMESPACE__ . '\Macros::getIcon($basePath, %node.array);');
		$set->addMacro('iss','echo ' . __NAMESPACE__ . '\Macros::issCompile($basePath, %node.array);');
		//$set->addMacro('thumbnail','echo ' . __NAMESPACE__ . '\Macros::thumbnail($dataPath, %node.array);');
		
		/*
		Pokud makro není párové, třetí parametr metody addMacro() vynecháme.

		PHP kód uváděný ve druhém a třetím parametru může obsahovat zástupné symboly:
		%node.word – vloží první argument makra
		%node.array – vloží argumenty makra naformátované jako PHP pole
		%node.args – vloží argumenty makra naformátované jako PHP kód
		%escape(...) – nahradí za aktuální escapovací funkcí
		%modify(...) – nahradí sérií modifikátorů

		Příklad:
			$set->addMacro('if', 'if (%node.args):', 'endif');
		*/			
	}

	public static function setupTemplate($template)
	{
		$template->registerHelper('filetype', __NAMESPACE__ . '\Macros::filetype');
		$template->registerHelper('filesize', __NAMESPACE__ . '\Macros::filesize');
		$template->registerHelper('fileicon', __NAMESPACE__ . '\Macros::fileicon');
		$template->registerHelper('filename', __NAMESPACE__ . '\Macros::filename');
		$template->registerHelper('fileurl', __NAMESPACE__ . '\Macros::fileurl');
		$template->registerHelper('cke_thumbnails', __NAMESPACE__ . '\Macros::cke_thumbnails');
		
		
		/* Texy! for articles. */
		$texy = new Texy();
		$texy->encoding = 'utf-8';
        $texy->allowedTags = Texy::NONE;
        $texy->allowedStyles = Texy::NONE;
        $texy->setOutputMode(Texy::HTML5);
		$texy->headingModule->top = 2;
		$texy->headingModule->generateID = true;
		
		// Zabezpečení
		$texy->urlSchemeFilters[Texy::FILTER_ANCHOR] = '#https?:|ftp:|mailto:|xmpp:#A';
		$texy->urlSchemeFilters[Texy::FILTER_IMAGE] = '#https?:#A';		
		// ...
		// TODO: Nastavit texy pro články.
		
		$template->registerHelper('texy', callback($texy, 'process'));
		$template->registerHelper('texy_title', callback($texy, 'processTypo'));
		
		return $template;
    }

	/**
	 * @param string
	 * @param string
	 * @return bool
	 */
	private static function issCheck($dir, $out) {
		if (!file_exists($out)) {
			return TRUE;
		}
		$time = filemtime($out);
		$it = new \DirectoryIterator($dir);
		foreach ($it as $file) {
			if ($file->isFile() && $file->getMTime() > $time) {
				return TRUE;
			}
		}
	}

	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function issCompile($basePath, $data)
	{				
		$name = $data[0];
		$media = isset($data[1]) ? $data[1] : "screen,projection,tv";

		// FIXME: write it better
		$wwwDir = __DIR__ . "/../";
		
		$in = $wwwDir . "/iss/" . $name . ".iss";	
		$dir = $wwwDir . "/css";
		if (!file_exists($dir) && !mkdir($dir))
			throw new \RuntimeException("Can't create '$dir' directory for css.");
		
		$out = $dir . '/' . $name . '.css';		
		
		if (self::issCheck(dirname($in), $out)) {
			$ivory = new \Ivory\Compiler();
			$ivory->outputDirectory = $dir;
			//$ivory->setDefaultUnit('px');
			$ivory->addFunction('img', function (array $value) {
				if (isset($value[0]) && $value[0] == 'string') {
					return array('function', 'url', array(array('expression', array('string', "'" . "../images/" . "'"), array('binary', '.'), $value)));
				}
			});
			$ivory->addFunction('font', function (array $value) {
				if (isset($value[0]) && $value[0] == 'string') {
					return array('expression', array('string', "'" . "fonts/" . "'"), array('binary', '.'), $value);
				}
			});
			
			$ivory->addFunction('colorEdit', function (array $value, $color, $substract = false) {
				if (isset($value[0]) && $value[0] == 'color') {
					
					if ($substract) {
						return array(
							'color',
							$value[1] - $color[1],
							$value[2] - $color[2],
							$value[3] - $color[3],
							1
						);
					} else {
						return array(
							'color',
							$value[1] + $color[1],
							$value[2] + $color[2],
							$value[3] + $color[3],
							1
						);
					}
				}
			});
			
			$ivory->addFunction('colorAdd', function (array $value, $color) {
				if (isset($value[0]) && $value[0] == 'color') {
					
					return array(
						'color',
						min(max($value[1] + $color[1], 0),255),
						min(max($value[2] + $color[2], 0),255),
						min(max($value[3] + $color[3], 0),255),
						1
					);
				}
			});
			
			$ivory->compileFile($in);
		}
		
		$ret = \Nette\Utils\Html::el("link");
		$ret->rel = "stylesheet";
		$ret->href = $basePath . '/css/' . $name . '.css?t=' . filemtime($out);
		$ret->media = $media;
		
		return $ret;
	}
	
	public static function getIcon($basePath, $params) {
		$name = array_shift($params);
		if (strpos($name, '.') === false) // if filename is without extension, add it
			$name .= ".png";
		
		$img = \Nette\Utils\Html::el("img");
		$img->src = $basePath . "/images/icons/" . $name;
		
		$img->addAttributes($params);
				
		if (isset($params["class"]))
			$img->class .= " ";		
		$img->class .= "icon";
		
		return $img;
	}	
	
	public static function cke_thumbnails($text)
	{
		$path = dirname(__FILE__) . '/..';
		$path = UPLOAD_DIR . '/..';

		$offset = 0;
		while (($pos = strpos($text, '<img ', $offset)) !== FALSE) {

			$tag = substr($text, $pos, strpos($text, '/>', $pos + 5) - $pos + 2);
			$offset += strlen($tag);

			$search = strpos($tag, 'src="');
			if ($search === FALSE) continue;
			$search += 5;
			$url_pos = $search;
			$url = substr($tag, $search, strpos($tag, '"', $search) - $search);

			if (substr($url, 0, 8) != '/upload/') continue;
			$info = @getimagesize($path . $url);
			if ($info === FALSE) continue;

			if (($search = strpos($tag, 'width: ')) !== FALSE) {
				$search += 7;
				$width = (int) substr($tag, $search, strpos($tag, 'px', $search) - $search);
			} else {
				$width = $info[0];
			}

			if (($search = strpos($tag, 'height: ')) !== FALSE) {
				$search += 8;
				$height = (int) substr($tag, $search, strpos($tag, 'px', $search) - $search);
			} else {
				$height = $info[1];
			}

			if ($width <= 0 || $width >= 1000 || $height <= 0 || $height >= 1000) continue;

			$info = pathinfo($url);

			if (!in_array(strtolower($info['extension']), array('jpg', 'jpeg', 'png', 'gif'))) continue;
			
			$thumb = '/data/thumbs/' /*. substr($info['dirname'], 8) . '/'*/ . $info['filename'] . '_' . $width . 'x' . $height . '.' . $info['extension'];
			
			if (!file_exists($path . $thumb) || filemtime($path . $url) > filemtime($path . $thumb)) {
				@mkdir(pathinfo($path . $thumb, PATHINFO_DIRNAME), 0777, TRUE);
				$img = \Nette\Image::fromFile($path . $url);
				$img->resize($width, $height, \Nette\Image::FILL);
				$x = $img->width > $width ? floor(($img->width - $width) / 2) : 0;
				$y = $img->height > $height ? floor(($img->height - $height) / 2) : 0;

				$img->crop($x, $y, $width, $height);

				$img->save($path . $thumb);
			}

			$new = '<a href="' . $url . '" class="colorbox" rel="cke-images">' . substr_replace($tag, $thumb, $url_pos, strlen($url)) . '</a>';

			$text = substr_replace($text, $new, $pos, strlen($tag));
			$offset = $offset + strlen($new) - strlen($tag);
		}

		return $text;
	}
	
	public static function thumbnail($dataUrl, array $data) {				
		$file = UPLOAD_DIR . $data[0];
		
		$file = urldecode($file);		
		
		if (!is_file($file)) {
			//throw new \InvalidArgumentException("Invalid or not readable file '$file'.");
			
			//return '<img src="/images/picture.jpg" alt="" class="thumbnail">';		
			return NULL;			
		}
		if (!isset($data["width"]) && !isset($data["height"])) {
			list($data["width"], $data["height"], $type) = getimagesize($file);
		} else {			
			list(,, $type) = getimagesize($file);
		}

		$dir = DATA_DIR . "/thumbs";
		// TODO: path thumb into right subfolder...
		$name = basename($file) . '_' . $data["width"] . 'x' . $data["height"] . image_type_to_extension($type);

		$thumbnail = $dir . '/' . $name;
		if (!file_exists($thumbnail)) {
			if (!file_exists($dir) && !mkdir($dir))
				throw new \RuntimeException("Can't create '$dir' directory for thumbnails.");

			if (!isset($data["flags"])) {
				$data["flags"] = \Nette\Image::FILL;
			}
			//\Nette\Debug::tryError();
			$image = \Nette\Image::fromFile($file);
			//if (\Nette\Debug::catchError()) die(1);

			$image->resize($data["width"], $data["height"], $data["flags"]);					
			$x = $image->width > $data["width"] ? floor(($image->width - $data["width"]) / 2) : 0;
			$y = $image->height > $data["height"] ? floor(($image->height - $data["height"]) / 2) : 0;

			$image->crop($x, $y, $data["width"], $data["height"]);

			$image->save($thumbnail, NULL, $type);
		}

		$img = \Nette\Utils\Html::el("img");
		$img->src = $dataUrl . "/thumbs/" . htmlspecialchars($name);

		$img->class = "thumbnail";
		if (isset($data["class"]))
			$img->class .= " " . $data["class"];

		$img->alt = isset($data["alt"]) ? $data["alt"] : "thumbnail";

		if (isset($data["title"]))
			$img->title = $data["title"];

		// TODO: $img->addAttributes($params)
			
		return $img;
	}
	
	public static function fileurl($filePath, $file) {
		return $filePath . $file;
	}
	
	public static function fileexists($file) {
		$file = UPLOAD_DIR . urldecode($file);
		return (is_file($file));
	}
	
	public static function filesize($file) {	
		$size = @filesize(FILES_DIR . "/" . urldecode($file));
		return \Nette\Templating\Helpers::bytes($size);
	}
	
	public static function filetype($file) {
		$types = array(
			'pdf' => "PDF dokument",
			'document' => "Dokument",
			'workbook' => "SeŇ°it",
			'presentation' => "Prezentace",
			'image' => "Obr?°zek",
			'archive' => "Archiv",
			'video' => "Video",
			'audio' => "Audio",
			'program' => "Program",
			'text' => "Textov?Ĺ soubor",
			'unknown' => "Nezn?°m?Ĺ soubor",			
		);
		
		$ext = strtolower(pathinfo(UPLOAD_DIR . $file, PATHINFO_EXTENSION));
		return $types[self::extension($ext)];
	}
	
	public static function filename($file) {
		return pathinfo(UPLOAD_DIR . urldecode($file), PATHINFO_BASENAME);
	}
	
	public static function fileicon($file) {				
		$ext = strtolower(pathinfo(UPLOAD_DIR . urldecode($file), PATHINFO_EXTENSION));

		return (string) \Nette\Utils\Html::el('img', array(
			'src' => "/images/icons/filetypes/" . self::extension($ext) . ".png",
			'alt' => "Ikona - " . self::filetype($file),
			//'class' => 'icon',
		));
	}
	
	private static function extension($ext) {
		switch ($ext) {
            case 'pdf':
                return 'pdf';
            case 'doc':
            case 'docx':
            case 'rtf':
                return 'document';
            case 'xls':
            case 'xlsx':
            case 'csv':
                return 'workbook';
            case 'ppt':
            case 'pptx':
                return 'presentation';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'psd':
                return 'image';
            case 'zip':
            case 'rar':
            case 'tar':
            case 'gz':
            case 'tgz':
            case '7z':
                return 'archive';
            case 'avi':
            case 'mkv':
            case 'mlv':
            case 'mp4':
            case 'sfw':
            case 'wmv':
                return 'video';
            case 'mp3':
            case 'wma':
            case 'flac':
                return 'audio';
            case 'exe':
                return 'program';
            case 'txt':
                return 'text';
            default:
                return 'unknown';
        }
	}	
	
	private static function fixTime($time) {
/*		if (!$time) {
			return FALSE;
		} else*/if (is_numeric($time)) {
            return (int) $time;
        } elseif ($time instanceof DateTime) {
            return $time->format('U');
        } else {
            return strtotime($time);
        }
	}
	
	public static function age($time) {
		if (!$time) {
            return false;
        } else {
			$time = self::fixTime($time);
		}
		
		if (!$time || $time > time()) {
			return false;
		}
		
		// TODO: Lepší výpočet věku?
		return floor((date("Ymd") - date("Ymd", strtotime($datum))) / 10000);
	}
	
    public static function timeAgoInWords($time)
    {
		if (!$time) {
            return false;
        } else {
			$time = self::fixTime($time);
		}

        $delta = time() - $time;

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);
            if ($delta == 0) return 'za okamžik';
            if ($delta == 1) return 'za minutu';
            if ($delta < 45) return 'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
            if ($delta < 90) return 'za hodinu';
            if ($delta < 1440) return 'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
            if ($delta < 2880) return 'zítra';
            if ($delta < 43200) return 'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
            if ($delta < 86400) return 'za měsíc';
            if ($delta < 525960) return 'za ' . round($delta / 43200) . ' ' . self::plural(round($delta / 43200), 'měsíc', 'měsíce', 'měsíců');
            if ($delta < 1051920) return 'za rok';
            return 'za ' . round($delta / 525960) . ' ' . self::plural(round($delta / 525960), 'rok', 'roky', 'let');
        }

        $delta = round($delta / 60);
        if ($delta == 0) return 'před okamžikem';
        if ($delta == 1) return 'před minutou';
        if ($delta < 45) return "před $delta minutami";
        if ($delta < 90) return 'před hodinou';
        if ($delta < 1440) return 'před ' . round($delta / 60) . ' hodinami';
        if ($delta < 2880) return 'včera';
        if ($delta < 43200) return 'před ' . round($delta / 1440) . ' dny';
        if ($delta < 86400) return 'před měsícem';
        if ($delta < 525960) return 'před ' . round($delta / 43200) . ' měsíci';
        if ($delta < 1051920) return 'před rokem';
        return 'před ' . round($delta / 525960) . ' lety';
    }

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    private static function plural($n)
    {
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

}