<?php
/**
* Mouse Over Zoom for Joomla! Plugin
* @version 1.3.3
* @author Joomla Boat <support@joomlaboat.com>
* @link https://joomlaboat.com
* @license    GNU General Public License version 2 or later; see LICENSE.txt */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgSystemMouseOverZoom extends CMSPlugin
{
	protected $app;

	public function onAfterRender()
	{
		if($this->app->isClient('site'))
		{
			//$output = JFactory::getApplication()->getBody();
			$output = $this->app->getBody();
			print_r($output);
			echo '$output=***'.$output.'%%%';
						
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
