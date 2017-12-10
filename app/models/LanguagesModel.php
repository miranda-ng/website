<?php

namespace Models;

use Macros;
use Nette\Database\Context;
use Nette\Http\Request;

/**
 * @table languages
 */
final class LanguagesModel extends BaseModel
{
	const LANG_DEFAULT = "en";
	const WIKI_LINK = "https://wiki.miranda-ng.org";

	/** @var Request */
	private $httpRequest;

	public function __construct(Context $database, Request $httpRequest) {
		parent::__construct($database);

		$this->httpRequest = $httpRequest;
	}

	public function getDefaultLanguage() {
		$langs = $this->getLanguages()->fetchPairs(NULL, "code");
		return $this->httpRequest->detectLanguage($langs) ?: self::LANG_DEFAULT;
	}

	public function getLanguages() {
		return $this->getTable()->order("code = ? DESC, code", self::LANG_DEFAULT);
	}

	public function getWikiLink($lang) {
		$item = $this->getTable()->get($lang);
		if (!$item || !$item->wiki_link) {
			return self::WIKI_LINK;
		}

		return Macros::GetWikiLink($item->wiki_link);
	}

}
