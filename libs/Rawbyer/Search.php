<?php

namespace Rawbyer;

final class Search extends \Nette\Object {

	public function parseSearch($word, $trim = '"', $minLength = 2, $maxCount = 10) {
		$trim = function($item) use ($trim) {
			return trim($item, $trim);
		};
		$filter = function($item) use ($minLength) {
			return mb_strlen($item) >= $minLength;
		};
		$matches;
		preg_match_all('/"[^"]+?"|[^\s\\"]+/si', $word, $matches); // získá jednotlivé slova (nebo spojení "slovo slovo")
		$arr = array_map($trim, $matches[0]); // zruší " na začátku a konci
		$arr = array_filter($arr, $filter);
		$arr = array_unique($arr);
		return array_slice($arr, 0, $maxCount);
	}

	public function prepareSearch($words, $delimiter = "/") {
		$words = array_map(function($item) use ($delimiter) {
			return preg_quote($item, $delimiter);
		}, $words);
		return implode("|", $words);
	}

	public function highlight($text, $word, $truncate = NULL)
	{
		$vys = $this->parseSearch($word);

		// pokud máme omezenou délku...
		if ($truncate) {
			$pos = false;
			$wordlen = 0;

			// vyhledáme slovo které je nejdříve v řetězci
			foreach ($vys as $word) {
				$tmp = mb_stripos($text, $word, 0);
				if ($tmp !== false && ($pos === false || $pos > $tmp)) {
					$pos = $tmp;
					$wordlen = mb_strlen($word);
				}
			}

			// pokud jsme nějaké našli, a nachází se v řetězci dále než je naše požadovaná délka
			if ($pos !== false && $truncate < ($pos+$wordlen+1)) {
				$text = "…" . mb_substr($text, (int)($pos - ($truncate / 2))); // ořízneme začátek tak, že hledané slovo bude v polovině výsledného řetězce
			}

			$text = \Nette\Utils\Strings::truncate($text, $truncate); // zkrátíme celou délku
		}

		$words = $this->prepareSearch($vys);

		return preg_replace("/$words/si",'<span class="highlight">$0</span>', $text);
	}

}