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
class IfTestHelper implements Helper
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
        $matches = array();
        preg_match_all('/(["\'])(?:\\\\.|[^\\\\\1])*?\1|\S+/', $args, $matches, PREG_SET_ORDER);

        $replaces = array();
        $first = true;
        $condition = "";

        foreach ($matches as $match){
            if(!$match[0])
                continue;

            if($first){
                $f_char = substr($match[0], 0, 1);
                if($f_char == "'" || $f_char == "\"")
                    $condition = substr($match[0], 1, -1);
                else
                    $condition = $match[0];
                $first = false;
                continue;
            }

            $values = split("=", $match[0]);
            $replaces[$values[0]] = $values[1];
        }

        $matches = array();
        preg_match_all('/(["\'])(?:\\\\.|[^\\\\\1])*?\1|\S+/', $condition, $matches, PREG_SET_ORDER);
        $condition = "return";
        foreach ($matches as $match){
            if(! $match[0])
                continue;

            $value = $match[0];

            if(array_key_exists($value, $replaces))
                $value = $replaces[$value];

            try {
                $value = "\"".$context->get($value, true) ."\"";
            } catch(\Exception $e) {
            }

            $condition .= " " . $value;
        }

        $condition .= ";";

        $tmp = eval($condition);

        $context->push($context->last());
        if ($tmp) {
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard($context);
        } else {
            $template->setStopToken('else');
            $template->discard($context);
            $template->setStopToken(false);
            $buffer = $template->render($context);
        }
        $context->pop();

        return $buffer;
    }
}
