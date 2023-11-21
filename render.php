<?php
/**
 * Mouse Over Zoom Joomla!
 * @version 1.3.4
 * @author Joomla Boat <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

class MouseOverZoomRender
{
	public static function csv_explode($delim = ',', $str = '', $enclose = '"', $preserve = false)
	{
		$resArr = array();
		$n = 0;
		$expEncArr = explode($enclose, $str);
		foreach ($expEncArr as $EncItem) {
			if ($n++ % 2) {
				$resArr[] = array_pop($resArr) . ($preserve ? $enclose : '') . $EncItem . ($preserve ? $enclose : '');
			} else {
				$expDelArr = explode($delim, $EncItem);
				$resArr[] = array_pop($resArr) . array_shift($expDelArr);
				$resArr = array_merge($resArr, $expDelArr);
			}
		}
		return $resArr;
	}

	public static function getListToReplace($par, &$options, $text, $qtype, $separator = ':', $quote_char = '"')
	{
		$fList = array();
		$l = strlen($par) + 2;

		$offset = 0;
		do {
			if ($offset >= strlen($text))
				break;

			$ps = strpos($text, $qtype[0] . $par . $separator, $offset);
			if ($ps === false)
				break;

			if ($ps + $l >= strlen($text))
				break;

			$quote_open = false;

			$ps1 = $ps + $l;
			$count = 0;
			while (1) {

				$count++;
				if ($count > 100)
					die;

				if ($quote_char == '')
					$peq = false;
				else {
					while (1) {
						$peq = strpos($text, $quote_char, $ps1);

						if ($peq > 0 and $text[$peq - 1] == '\\') {
							// ignore quote in this case
							$ps1++;

						} else
							break;
					}
				}

				$pe = strpos($text, $qtype[1], $ps1);

				if ($pe === false)
					break;

				if ($peq !== false and $peq < $pe) {
					//quote before the end character

					if (!$quote_open)
						$quote_open = true;
					else
						$quote_open = false;

					$ps1 = $peq + 1;
				} else {
					if (!$quote_open)
						break;

					$ps1 = $pe + 1;

				}
			}

			if ($pe === false)
				break;

			$noteStr = substr($text, $ps, $pe - $ps + 1);

			$options[] = trim(substr($text, $ps + $l, $pe - $ps - $l));
			$fList[] = $noteStr;

			$offset = $ps + $l;

		} while (!($pe === false));

		//for these with no parameters
		$ps = strpos($text, $qtype[0] . $par . $qtype[1]);
		if (!($ps === false)) {
			$options[] = '';
			$fList[] = $qtype[0] . $par . $qtype[1];
		}

		return $fList;
	}

	protected static function strip_html_tags_textarea($text)
	{
		$pattern1 = array('@<textarea[^>]*?>.*?</textarea>@siu');
		$pattern2 = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', "$0", "$0", "$0", "$0", "$0", "$0", "$0", "$0");
		return preg_replace($pattern1, $pattern2, $text);
	}

	protected static function ApplyImageClass($text, &$text_original, $applyToClass, $defaultZoomFactor, $bigImagePostfix, $triggerEvent, &$imageList, &$foundCount, $defaultDegree)
	{
		if ($applyToClass == '')
			$applyToClass = 'mouseoverzoom';

		$foundCount = 0;
		$pl = 0;

		do {

			if (!function_exists("stripos")) {
				$p = strpos($text, '<img', $pl);
				if ($p === false)
					$p = strpos($text, '<IMG', $pl);
			} else
				$p = stripos($text, '<img', $pl);

			if (!($p === false)) {

				$pe = strpos($text, '>', $p);
				if (!($pe == false)) {

					$tag = substr($text, $p, $pe - $p + 1);
					$classname = MouseOverZoomRender::getAttribute('class', $tag);

					if ($classname == $applyToClass) {
						$src = MouseOverZoomRender::getAttribute('src', $tag);
						$width = (int)MouseOverZoomRender::getAttribute('width', $tag);
						$height = (int)MouseOverZoomRender::getAttribute('height', $tag);
						$zoomFactor = (float)MouseOverZoomRender::getAttribute('zoom', $tag);
						$img_alt = MouseOverZoomRender::getAttribute('alt', $tag);
						$img_title = MouseOverZoomRender::getAttribute('title', $tag);

						$newTag = MouseOverZoomRender::getMOZCode($src, $bigImagePostfix, $triggerEvent, $defaultZoomFactor, $imageList, $width, $height, $zoomFactor, $img_alt, $img_title, $defaultDegree);
						if ($newTag != '') {
							$foundCount++;
							$text_original = str_replace($tag, $newTag, $text_original);
						}
					}
				}
			}
			$pl = $p + 4;
		} while (!($p === false));

		return true;
	}

	protected static function getAttribute($attrib, $tag)
	{
		//get attribute from html tag
		//$re = '/'.$attrib.'=["\']?([^"\' ]*)["\' ]/is';// - without whitespace
		$re = '/' . $attrib . ' *= *["\']?([^"\']*)["\' ]/is';// - with whitespace

		preg_match($re, $tag, $match);
		if ($match) {
			return urldecode($match[1]);
		} else {
			return false;
		}
	}

	protected static function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		return $randomString;
	}

	protected static function getMOZCode($src, $bigimagepostfix, $triggerevent, $defaultzoomfactor, &$imageList, $width, $height, $zoomFactor, $img_alt, $img_title, $defaultrotatedegree)
	{
		$src_arr = MouseOverZoomRender::csv_explode(',', $src);

		if ($zoomFactor == 0) {
			if (isset($src_arr[4])) {
				if ((float)$src_arr[4] > 1)
					$zoomFactor = (float)$src_arr[4];
				else
					$zoomFactor = $defaultzoomfactor;
			} else
				$zoomFactor = $defaultzoomfactor;
		}

		if (isset($src_arr[5]) and $src_arr[5] != '')
			$rotateDegree = (int)$src_arr[5];
		else
			$rotateDegree = $defaultrotatedegree;

		if (count($src_arr) > 1) {

			$smallImage = $src_arr[0];

			if (isset($src_arr[1]))
				$bigImage = $src_arr[1];
			else
				$bigImage = $src;

			if (isset($src_arr[2])) {
				$aLink = $src_arr[2];
				$aLink = str_replace('http:/', 'http://', $aLink);
				$aLink = str_replace('https:/', 'https://', $aLink);
			} else
				$aLink = '';

			if (isset($src_arr[3]))
				$link_target = $src_arr[3];
			else
				$link_target = '';

		} else {
			$smallImage = $src;
			$bigImage = $src;
			$aLink = '';
			$link_target = '';
		}

		if ($bigimagepostfix != '' and ($bigImage == $smallImage or $bigImage == ''))
			$bigImage = str_replace('.', $bigimagepostfix . '.', $smallImage);

		if ($triggerevent == "moc")
			$aLink = '';


		//check the image

		$isImageOk = false;

		$sz = MouseOverZoomRender::getImageSize($smallImage, $width, $height);
		if (count($sz) == 0) {

			$app = Factory::getApplication();
			$app->enqueueMessage('MouseOverZoom Plugin: Image not found or corrupted or external and there is no permissions to get its info.', 'error');

		} else {
			//Big Image
			if ($bigImage != '') {
				$sz_big = MouseOverZoomRender::getImageSize($bigImage, 0, 0);
				if (count($sz_big) == 0)
					$bigImage = $smallImage;
			} else
				$bigImage = $smallImage;

			$isImageOk = true;
		}

		if ($isImageOk) {

			//Apply Mouse Over to this Image

			$padding = 0;

			$classname = '';
			$isNew = true;
			foreach ($imageList as $img) {
				// $img[0] - classname=$img[0];
				// $img[1] - width=$img[1];
				// $img[2] - height=$img[2];
				// $img[3] - small image file name
				// $img[4] - big image file name
				// $img[5] - zoomfactor_=$img[5];
				// $img[9] - rotate_degree_=$img[9];
				//if($img[3]==$smallImage and $img[4]==$bigImage and $img[5]==$zoomfactor )
				//{
				//$isNew=false;
				//$classname=$img[0];
				//break;
				//}
			}

			if ($isNew) {
				$classname .= 'moz_thumb_' . self::generateRandomString(6) . '_' . count($imageList);
				$imageList[] = array($classname, $sz[0], $sz[1], $smallImage, $bigImage, $zoomFactor, 0, 0, 0, $rotateDegree);
			}

			if ($img_title == '')
				$img_title = $img_alt;
			else
				if ($img_alt == '')
					$img_alt = $img_title;

			$gcss = 'min-width:auto!important;max-width:auto!important;'
				. 'min-height:auto!important;max-height:auto!important;'
				. 'padding:0;margin:0;border:0;width:' . $sz[0] . 'px;height:' . $sz[1] . 'px;';

			$newTag = ''
				. '<div class="MouseOverZoomPlugin" id="' . $classname . '_id" style="position: relative; width: ' . $sz[0] . 'px; height: ' . $sz[1] . 'px;">'
				. '<img src="' . $smallImage . '" id="' . $classname . '_small" alt="' . $img_alt . '" title="' . $img_title . '" style="visibility:visible;' . $gcss . '" width="' . $sz[0] . '" height="' . $sz[1] . '" />'
				. '<div class="' . $classname . '"'
				. ' style="position: absolute; top: 0; left: 0; width: ' . ($sz[0] + $padding * $zoomFactor) . 'px; height: ' . ($sz[1] + $padding * $zoomFactor) . 'px;'
				. ($triggerevent == "moc" ? 'cursor:pointer;' : '')
				. '">';

			$img2tag = '<img src="' . $bigImage . '" id="' . $classname . '_big" width="' . $sz[0] . '" height="' . $sz[1] . '" alt="' . $img_alt . '" title="' . $img_title . '"'
				. ' style="z-index:0;visibility:hidden;' . $gcss
				. ($triggerevent == "moc" ? 'cursor:pointer;' : '')
				. '" />';

			if ($triggerevent == 'moc') {
				$newTag .= $img2tag;
			} else {
				if ($aLink != '')
					$newTag .= '<a href="' . $aLink . '"' . ($link_target != '' ? ' target="' . $link_target . '" ' : '') . '>' . $img2tag . '</a>';
				else
					$newTag .= '<a>' . $img2tag . '</a>';
			}

			$newTag .= '</div></div>';


		} else
			$newTag = '';

		return $newTag;
	}

	protected static function getImageSize($smallimage, $width, $height)
	{
		if ($smallimage == '')
			return array();

		if (!(strpos($smallimage, 'http') === false)) {
			//external
			echo '<!-- ';
			$sz = @getimagesize($smallimage);
			echo ' -->';

			if ($sz[0] < 1 and $sz[1] < 1) {
				//image not found or not permitted to get external image info
				if ($width < 1 or $height < 1) {
					//cannot handle unknown dimension image

					return array();
				}
			}

		} else {
			//local

			if (!file_exists($smallimage)) {
				if ($smallimage[0] == '/') {
					$smallimage = substr($smallimage, 1);
					if (!file_exists($smallimage))
						return array(); //image not found
				} else
					return array(); //image not found
			}

			$sz = getimagesize($smallimage);
			if ($sz[0] < 1 and $sz[1] < 1) {
				//image is corrupted
				return array();
			}

		}

		$customWidth = $width;
		$customHeight = $height;
		if ($customWidth > 0) {

			if ($customHeight == 0)
				$customHeight = $customWidth * $sz[0] / $sz[1];

			$sz[0] = $customWidth;
			$sz[1] = $customHeight;
		} else {
			if ($customHeight > 0) {

				if ($customWidth == 0)
					$customWidth = $customHeight * $sz[1] / $sz[0];

				$sz[0] = $customWidth;
				$sz[1] = $customHeight;
			}
		}

		return $sz;
	}

	protected static function getJSCode($jqueryLibraryLink, &$imagelist, $triggerevent, $checkwindowsize, $degree)
	{
		$CodeToGo = '';

		if ($jqueryLibraryLink == '')
			$jqueryLibraryLink = 'https://code.jquery.com/jquery.min.js';

		if ($jqueryLibraryLink != '-') {
			$CodeToGo = '
			<script src="' . $jqueryLibraryLink . '"></script>
			<script src="' . JURI::root(true) . '/plugins/content/mouseoverzoom/mouseoverzoom.js"></script>
			';
		}

		$CodeToGo .= '

		<style>
			.MouseOverZoomPlugin img {min-height: inherit !important;max-height: none !important;min-width: inherit !important;max-width: none !important;}
		</style>
		<script>
			jQuery(document).ready(function(){
		';

		foreach ($imagelist as $img) {
			$classname = $img[0];
			$width = (int)$img[1];
			$height = (int)$img[2];

			$zoomFactor = (float)$img[5];

			if (isset($img[9]) and $img[9] != '')
				$degree = $img[9];

			//Larger thumbnail preview

			if ($triggerevent == 'moc') {
				$CodeToGo .= '
				var ' . $classname . '_State=0;
				jQuery("#' . $classname . '_id").click(function(){
					' . $classname . '_State=MOZDoTheJob(this,"' . $checkwindowsize . '","' . $classname . '",' . $classname . '_State,"' . $triggerevent . '",' . (int)$width . ',' . (int)$height . ',' . (float)$zoomFactor . ',' . (int)$degree . ');
				});
			';
			} else {
				$CodeToGo .= '
				var ' . $classname . '_State=0;
				jQuery("div.' . $classname . '").hover(
					function() {
						MOZDoTheJob(this,"' . $checkwindowsize . '","' . $classname . '",' . $classname . '_State,"' . $triggerevent . '",' . (int)$width . ',' . (int)$height . ',' . (float)$zoomFactor . ',' . (int)$degree . ');
					},
					function() {
						MOZUndoTheJob(this,"' . $classname . '",' . $width . ',' . $height . ',' . $degree . ');
					}
					);
			';
			}
		}

		$CodeToGo .= '
		});
		</script>
		';
		return $CodeToGo;
	}

	function ApplyPlugin(&$text_original, &$jsCode, $jQueryLibraryLink, $checkWindowSize, $avoidTextArea, $applyToClass, $defaultZoomFactor,
	                     $bigImagePostfix, $triggerEvent, $defaultDegree)
	{
		if ($defaultZoomFactor < 1 or $defaultZoomFactor > 10)
			$defaultZoomFactor = 2;

		if ($avoidTextArea)
			$text = MouseOverZoomRender::strip_html_tags_textarea($text_original);
		else
			$text = $text_original;

		$imageList = array();
		$foundCount = 0;

		MouseOverZoomRender::ApplyImageClass($text, $text_original, $applyToClass, $defaultZoomFactor, $bigImagePostfix, $triggerEvent, $imageList, $foundCount, $defaultDegree);
		MouseOverZoomRender::ApplyBracketMode($text, $text_original, $defaultZoomFactor, $bigImagePostfix, $triggerEvent, $imageList, $foundCount, $defaultDegree);

		if ($foundCount > 0)
			$jsCode = MouseOverZoomRender::getJSCode($jQueryLibraryLink, $imageList, $triggerEvent, $checkWindowSize, $defaultDegree);
	}

	function ApplyBracketMode($text, &$text_original, $defaultzoomfactor, $bigimagepostfix, $triggerevent, &$imagelist, &$foundCount, $defaul_degree)
	{
		$options = array();

		//{moz=image_small,image_big,link,link_traget,zoom_factor,WIDTH,HEIGHT,IMG_ALT,IMG_TITLE,rotate_degree}
		$fList = MouseOverZoomRender::getListToReplace('moz', $options, $text, '{}', '=');

		$i = 0;
		foreach ($fList as $f) {
			if ($options[$i] != '') {
				$pair = MouseOverZoomRender::csv_explode(',', $options[$i]);

				if (isset($pair[5]))
					$width = (int)$pair[5];
				else
					$width = 0;

				if (isset($pair[6]))
					$height = (int)$pair[6];
				else
					$height = 0;

				if (isset($pair[7]))
					$img_alt = $pair[7];
				else
					$img_alt = '';

				if (isset($pair[8]))
					$img_title = $pair[8];
				else
					$img_title = '';

				if (isset($pair[9]) and $pair[9] != '')
					$rotate_degree = (int)$pair[9];
				else
					$rotate_degree = $defaul_degree;

				$newTag = MouseOverZoomRender::getMOZCode($options[$i], $bigimagepostfix, $triggerevent, $defaultzoomfactor, $imagelist, $width, $height, 0, $img_alt, $img_title, $rotate_degree);
				$text_original = str_replace($fList[$i], $newTag, $text_original);
				$foundCount++;
			}
			$i++;
		}

		return true;
	}
}
