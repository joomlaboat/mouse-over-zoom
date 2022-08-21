<?php
/**
 * Mouse Over Zoom for Joomla! Plugin
 * @version 1.3.3
 * @author Joomla Boat <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentMouseOverZoom extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        $output = $article->text;

        $jscode = '';

        require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'mouseoverzoom' . DIRECTORY_SEPARATOR . 'mouseoverzoom' . DIRECTORY_SEPARATOR . 'render.php');

        $mozr = new MouseOverZoomRender;

        $mozr->ApplyPlugin(
            $output,
            $jscode,
            $this->params->get('jquerylibrarylink'),
            $this->params->get('checkwindowsize'),
            $this->params->get('avoidtextarea'),
            $this->params->get('applytoclass'),
            $this->params->get('defaultzoomfactor'),
            $this->params->get('bigimagepostfix'),
            $this->params->get('triggerevent'),
            $this->params->get('method'),
            (int)$this->params->get('rotate')
        );

        if ($jscode != '')
            $output = str_replace('</head>', $jscode . '</head>', $output);

        $article->text = $output;
    }
}
/*
class plgSystemMouseOverZoom extends CMSPlugin
{
	protected $app;

	public function onAfterRender()
	{
		if($this->app->isClient('site'))
		{
			$output = $this->app->getBody();
						
			$jscode='';

			require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'mouseoverzoom'.DIRECTORY_SEPARATOR.'mouseoverzoom'.DIRECTORY_SEPARATOR.'render.php');

			$mozr=new MouseOverZoomRender;

			$mozr->ApplyPlugin(
				   $output,
				   $jscode,
				   $this->params->get('jquerylibrarylink'),
				   $this->params->get('checkwindowsize'),
				   $this->params->get('avoidtextarea'),
				   $this->params->get('applytoclass'),
				   $this->params->get('defaultzoomfactor'),
				   $this->params->get('bigimagepostfix'),
				   $this->params->get('triggerevent'),
				   $this->params->get('method'),
				   (int)$this->params->get('rotate')
			);

			if($jscode!='')
				$output=str_replace('</head>',$jscode.'</head>',$output);

			$this->app->setBody($output);
		}

	}

}//class
*/