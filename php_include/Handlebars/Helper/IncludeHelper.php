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
class IncludeHelper implements Helper
{
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

    public function execute(Template $template, Context $context, $args, $source)
    {
        preg_match_all('/(["\'])(?:\\\\.|[^\\\\\1])*\1|\S+/', $args, $matches, PREG_SET_ORDER);
        $path = "return";

        foreach($matches as $match){
            $value = $match[0];
            if($value != "."){
                try{
                    $value = "\"" . $context->get($value, true) . "\"";
                } catch(\Exception $e){
                }
            }

            $path .= " " . $value;
        }

        #$inc = file_get_contents(eval($path.";"));
        #$eng = new \Handlebars\Handlebars;
        #$inc = $eng->render($inc, $context);
        #return new \Handlebars\SafeString($inc);

        return new \Handlebars\SafeString(file_get_contents(eval($path.";")));
    }
}
