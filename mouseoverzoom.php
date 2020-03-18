<?php
/**
* Mouse Over Zoom for Joomla! Plugin
* @version 1.3.3
* @author Joomla Boat <support@joomlaboat.com>
* @link https://joomlaboat.com
* @license    GNU General Public License version 2 or later; see LICENSE.txt */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgSystemMouseOverZoom extends JPlugin
{

	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if($app->isSite())
		{
			$output = JResponse::getBody();
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

			JResponse::setBody($output);
		}

	}

}//class
