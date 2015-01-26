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

namespace Handlebars\Helper;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

/**
 * Handlebars halper interface
 *
 * @category  Xamin
 * @package   Handlebars
 * @copyright 2015 Authors
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   Release: @package_version@
 */
class BbcodeHelper implements Helper
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
                "openTag" => function($p, $c){
                    return "<a href=\"" . substr($p, 1) . "\">";
                },
                "closeTag" => function($p, $c){
                    return "</a>";
                }
            ),
            "b"=> array(
              "openTag" => function($params,$content) {
                return '<b>';
              },
              "closeTag" => function($params,$content) {
                return '</b>';
              }
            ),
            "i" => array (
              "openTag" => function($params,$content) {
                return '<i>';
              },
              "closeTag" => function($params,$content) {
                return '</i>';
              }
            ),
            "color" => array(
              "openTag" => function($params,$content) {
                $color = substr($params, 1);
                return '<span style="color: ' . $color . ';">';
              },
              "closeTag" => function($params,$content) {
                return '</span>';
              }
            ),
            "size" => array (
              "openTag" => function($params,$content) {
                $size = substr($params, 1);
                return '<span style="font-size: ' . $size . 'px;">';
              },
              "closeTag" => function($params,$content) {
                return '</span>';
              }
            ),
            "youtube" => array(
              "openTag" => function($params,$content) {
                return '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="http://www.youtube.com/embed/';
              },
              "closeTag" => function($params,$content) {
                return '?rel=0&amp;hd=1" frameborder="0" allowfullscreen></iframe></div>';
              }
            ),
            "vimeo" => array(
              "openTag" => function($params,$content) {
                return '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//player.vimeo.com/video/';
              },
              "closeTag" => function($params,$content) {
                return '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
              }
            ),
            "img" => array(
              "openTag" => function($params,$content) {
                //var myUrl = params.substr(1);
                return "<img src=\"";
              },
              "closeTag" => function($params,$content) {
                return "\"/>";
              }
            )
        );

        foreach($this->tagHelpers as $name => $value)
            $this->tagList[] = $name;

        $this->pbbRegExp = "/\\[(" . join("|", $this->tagList) . ")([ =][^\\]]*?)?\\]([^\\[]*?)\\[\/\\1\\]/i";
    }

    public function execute(Template $template, Context $context, $args, $source)
    {
        $text = htmlspecialchars($context->get($args, true), ENT_COMPAT, "UTF-8");
        while( $text !== ( $text = preg_replace_callback($this->pbbRegExp, function($matches){
            $lowTag = strtolower($matches[1]);
            if(array_key_exists($lowTag, $this->tagHelpers)){
                return  $this->tagHelpers[$lowTag]["openTag"]($matches[2], $matches[3]) . $matches[3] . $this->tagHelpers[$lowTag]["closeTag"]($matches[2], $matches[3]);
            }
        }, $text))){}

        return new \Handlebars\SafeString($text);
    }
}
