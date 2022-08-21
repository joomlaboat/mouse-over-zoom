<?php
/**
 * Mouse Over Zoom for Joomla! Plugin
 * @version 1.3.4
 * @author Joomla Boat <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentMouseOverZoom extends CMSPlugin
{
    public function onContentPrepare($context, $article, $params, $limitStart = 0)
    {
        $output = $article->text;
        $JSCode = '';

        require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'mouseoverzoom' . DIRECTORY_SEPARATOR . 'render.php');

        $MouseOverZoomRenderer = new MouseOverZoomRender;

        $MouseOverZoomRenderer->ApplyPlugin(
            $output,
            $JSCode,
            $this->params->get('jquerylibrarylink'),
            $this->params->get('checkwindowsize'),
            $this->params->get('avoidtextarea'),
            $this->params->get('applytoclass'),
            $this->params->get('defaultzoomfactor'),
            $this->params->get('bigimagepostfix'),
            $this->params->get('triggerevent'),
            (int)$this->params->get('rotate')
        );

        if ($JSCode != '')
        {
            $app = Factory::getApplication();
            $document = $app->getDocument();
            $document->addCustomTag($JSCode);
        }
        $article->text = $output;
    }
}
