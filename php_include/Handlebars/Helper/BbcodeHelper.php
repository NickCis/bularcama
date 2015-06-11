<?php
/**
 * This file is not part of Handlebars-php
 *
 * PHP version 5.3
 *
 * @category  Xamin
 * @package   Handlebars
 * @copyright 2015 Authors
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 */

//namespace Handlebars\Helper;

//use Handlebars\Context;
//use Handlebars\Helper;
//use Handlebars\Template;
require_once(dirname(__FILE__)."/../Context.php");
require_once(dirname(__FILE__)."/../Helper.php");
require_once(dirname(__FILE__)."/../Template.php");
require_once(dirname(__FILE__)."/../SafeString.php");

/**
 * Handlebars halper interface
 *
 * @category  Xamin
 * @package   Handlebars
 * @copyright 2015 Authors
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 */
function urlOpenTag($p, $c){
	return "<a href=\"" . substr($p, 1) . "\">";
}

function urlCloseTag($p, $c){
	return "</a>";
}

function bOpenTag($params,$content) {
	return '<b>';
}

function bCloseTag($params,$content) {
	return '</b>';
}

function iOpenTag($params,$content) {
	return '<i>';
}

function iCloseTag($params,$content) {
	return '</i>';
}

function colorOpenTag($params,$content) {
	$color = substr($params, 1);
	return '<span style="color: ' . $color . ';">';
}

function colorCloseTag($params,$content) {
	return '</span>';
}

function sizeOpenTag($params,$content) {
	$size = substr($params, 1);
	return '<span style="font-size: ' . $size . 'px;">';
}

function sizeCloseTag($params,$content) {
	return '</span>';
}

function youtubeOpenTag($params,$content) {
	return '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="http://www.youtube.com/embed/';
}

function youtubeCloseTag($params,$content) {
	return '?rel=0&amp;hd=1" frameborder="0" allowfullscreen></iframe></div>';
}

function vimeoOpenTag($params,$content) {
	return '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//player.vimeo.com/video/';
}

function vimeoCloseTag($params,$content) {
	return '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
}

function imgOpenTag($params,$content) {
	//var myUrl = params.substr(1);
	return "<img src=\"";
}

function imgCloseTag($params,$content) {
	return "\"/>";
}

class Handlebars_Helper_BbcodeHelper implements Handlebars_Helper
{
    protected $tagHelpers = array();

    protected $tagList = array();
    protected $pbbRegExp = "";

    /**
     * Execute the helper
     *
     * @param \Handlebars\Template $template The template instance
     * @param \Handlebars\Context  $context  The current context
     * @param array                $args     The arguments passed the the helper
     * @param string               $source   The source
     *
     * @return mixed
     */

    public function __construct()
    {
        $this->tagHelpers = array(
            "url" => array(
                "openTag" => urlOpenTag,
                "closeTag" => urlCloseTag
            ),
            "b"=> array(
              "openTag" => bOpenTag,
              "closeTag" => bCloseTag
            ),
            "i" => array (
              "openTag" => iOpenTag,
              "closeTag" => iCloseTag
            ),
            "color" => array(
              "openTag" => colorOpenTag,
              "closeTag" => colorCloseTag
            ),
            "size" => array (
              "openTag" => sizeOpenTag,
              "closeTag" => sizeCloseTag
            ),
            "youtube" => array(
              "openTag" => youtubeOpenTag,
              "closeTag" => youtubeCloseTag
            ),
            "vimeo" => array(
              "openTag" => vimeoOpenTag,
              "closeTag" => vimeoCloseTag
            ),
            "img" => array(
              "openTag" => imgOpenTag,
              "closeTag" => imgCloseTag
            )
        );

        foreach($this->tagHelpers as $name => $value)
            $this->tagList[] = $name;

        $this->pbbRegExp = "/\\[(" . join("|", $this->tagList) . ")([ =][^\\]]*?)?\\]([^\\[]*?)\\[\/\\1\\]/i";
    }

    function callback_preg($matches){
		$lowTag = strtolower($matches[1]);
		if(array_key_exists($lowTag, $this->tagHelpers)){
			return  $this->tagHelpers[$lowTag]["openTag"]($matches[2], $matches[3]) . $matches[3] . $this->tagHelpers[$lowTag]["closeTag"]($matches[2], $matches[3]);
		}
	}
    public function execute(Handlebars_Template $template, Handlebars_Context $context, $args, $source)
    {
        $text = htmlspecialchars($context->get($args, true), ENT_COMPAT, "UTF-8");
        while( $text !== ( $text = preg_replace_callback($this->pbbRegExp, array($this, "callback_preg"), $text))){}

        return new Handlebars_SafeString($text);
    }
}
