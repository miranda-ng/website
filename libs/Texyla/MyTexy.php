<?php

//require_once LIBS_DIR . "/Texy/texy.min.php";

use Nette\Application\UI, Nette\Utils\Strings;
use Nette\Templating\FileTemplate, Nette\Latte\Engine;

/**
 * My Texy
 *
 * @author Jan Marek
 * @license MIT
 */
class MyTexy extends Texy
{
	private $templatesDir;

	/**
	 * Construct
	 */
	public function __construct($wwwDir, $baseUrl)
	{
		parent::__construct();

		$this->templatesDir = __DIR__ . "/templates";

		// output
		$this->encoding = 'utf-8';
		$this->setOutputMode(self::HTML5);
		$this->htmlOutputModule->removeOptional = false;
		self::$advertisingNotice = false;

		// headings
		$this->headingModule->top = 2;
		$this->headingModule->generateID = true;
		$this->headingModule->balancing = TexyHeadingModule::FIXED;

		// phrases
		$this->allowed['phrase/ins'] = true;   // ++inserted++
		$this->allowed['phrase/del'] = true;   // --deleted--
		$this->allowed['phrase/sup'] = true;   // ^^superscript^^
		$this->allowed['phrase/sub'] = true;   // __subscript__
		$this->allowed['phrase/cite'] = true;   // ~~cite~~
		$this->allowed['deprecated/codeswitch'] = true; // `=code

		// images
		$this->imageModule->fileRoot = $wwwDir . "/files";
		$this->imageModule->root = $baseUrl . "/files/";

		// security
		$this->urlSchemeFilters[Texy::FILTER_ANCHOR] = '#https?:|ftp:|mailto:|xmpp:#A';
		$this->urlSchemeFilters[Texy::FILTER_IMAGE] = '#https?:#A';

		// smileys
		$this->allowed['emoticon'] = true;
		require $wwwDir . "/js/texyla/emoticons/texy/cfg.php";

		// flash, youtube.com, stream.cz, gravatar handlers
		$this->addHandler('image', array($this, 'youtubeHandler'));
		$this->addHandler('image', array($this, 'streamHandler'));
		$this->addHandler('image', array($this, 'flashHandler'));
		$this->addHandler("phrase", array($this, "netteLink"));
		$this->addHandler("phrase", array($this, "targetLink"));
		$this->addHandler('image', array($this, 'gravatarHandler'));
		$this->addHandler("image", array($this, "facebookHandler"));
	}



	/**
	 * Template factory
	 * @return Template
	 */
	private function createTemplate()
	{
		$template = new FileTemplate;
		$template->registerFilter(new Engine);
		return $template;
	}



	/**
	 * @param TexyHandlerInvocation  handler invocation
	 * @param string
	 * @param string
	 * @param TexyModifier
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function netteLink($invocation, $phrase, $content, $modifier, $link)
	{
		// is there link?
		if (!$link) return $invocation->proceed();

		$url = $link->URL;

		if (Strings::startsWith($url, "plink://")) {
			$url = substr($url, 8);
			list($presenter, $params) = explode("?", $url, 2);

			$arr = array();

			if ($params) {
				parse_str($params, $arr);
			}

			$link->URL = $this->presenter->link($presenter, $arr);
		}

		return $invocation->proceed();
	}


	/**
	 * @param TexyHandlerInvocation  handler invocation
	 * @param string
	 * @param string
	 * @param TexyModifier
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public static function targetLink($invocation, $phrase, $content, $modifier, $link) {
		// vychozí zpracování Texy
		$el = $invocation->proceed();

		// ověř, že $el je objekt TexyHtml a že jde o element 'a'
		if ($el instanceof TexyHtml && $el->getName() === 'a') {
			// uprav jej
			$el->attrs['target'] = '_blank';
		}

		return $el;
	}



	/**
	 * YouTube handler for images
	 *
	 * @example [* youtube:JG7I5IF6 *]
	 *
	 * @param TexyHandlerInvocation  handler invocation
	 * @param TexyImage
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function youtubeHandler($invocation, $image, $link)
	{
		$parts = explode(':', $image->URL, 2);

		if (count($parts) !== 2 || $parts[0] !== "youtube") {
			return $invocation->proceed();
		}

		$template = $this->createTemplate()->setFile($this->templatesDir . "/@youtube.latte");
		$template->id = $parts[1];
		if ($image->width) $template->width = $image->width;
		if ($image->height) $template->height = $image->height;

		return $this->protect((string) $template, Texy::CONTENT_BLOCK);
	}



	/**
	 * Flash handler for images
	 *
	 * @example [* flash.swf 200x150 .(alternative content) *]
	 *
	 * @param TexyHandlerInvocation  handler invocation
	 * @param TexyImage
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function flashHandler($invocation, $image, $link)
	{
		if (!Strings::endsWith($image->URL, ".swf")) {
			return $invocation->proceed();
		}

		$template = $this->createTemplate()->setFile($this->templatesDir . "/@flash.latte");
		$template->url = Texy::prependRoot($image->URL, $this->imageModule->root);
		$template->width = $image->width;
		$template->height = $image->height;
		if ($image->modifier->title) $template->title = $image->modifier->title;

		return $this->protect((string) $template, Texy::CONTENT_BLOCK);
	}



	/**
	 * User handler for images
	 *
	 * @example [* stream:98GDAS675G *]
	 *
	 * @param TexyHandlerInvocation  handler invocation
	 * @param TexyImage
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function streamHandler($invocation, $image, $link)
	{
		$parts = explode(':', $image->URL, 2);

		if (count($parts) !== 2 || $parts[0] !== "stream") {
			return $invocation->proceed();
		}

		$template = $this->createTemplate()->setFile($this->templatesDir . "/@stream.latte");
		$template->id = $parts[1];
		if ($image->width) $template->width = $image->width;
		if ($image->height) $template->height = $image->height;

		return $this->protect((string) $template, Texy::CONTENT_BLOCK);
	}



	/**
	 * Gravatar handler for images
	 *
	 * @example [* gravatar:user@example.com *]
	 *
	 * @param TexyHandlerInvocation  handler invocation
	 * @param TexyImage
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function gravatarHandler($invocation, $image, $link)
	{
		$parts = explode(':', $image->URL, 2);

		if (count($parts) !== 2 || $parts[0] !== "gravatar") {
			return $invocation->proceed();
		}

		$template = $this->createTemplate()->setFile($this->templatesDir . "/@gravatar.latte");
		$template->email = $parts[1];
		if ($image->width) $template->width = $image->width;
		if ($image->height) $template->height = $image->height;

		return $this->protect((string) $template, Texy::CONTENT_BLOCK);
	}

	/**
	 * Facebook video handler for images
	 *
	 * @example [* facebook:10200287993704048 *]
	 *
	 * @param TexyHandlerInvocation  handler invocation
	 * @param TexyImage
	 * @param TexyLink
	 * @return TexyHtml|string|FALSE
	 */
	public function facebookHandler($invocation, $image, $link)
	{
		$parts = explode(':', $image->URL, 2);

		if (count($parts) !== 2 || $parts[0] !== "facebook") {
			return $invocation->proceed();
		}

		$template = $this->createTemplate()->setFile($this->templatesDir . "/@facebook.latte");
		$template->id = $parts[1];
		if ($image->width) $template->width = $image->width;
		if ($image->height) $template->height = $image->height;

		return $this->protect((string) $template, Texy::CONTENT_BLOCK);
	}

}