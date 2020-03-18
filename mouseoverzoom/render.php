<?php
/**
* Mouse Over Zoom Joomla!
* @version 1.3.3
* @author Joomla Boat <support@joomlaboat.com>
* @link https://joomlaboat.com
* @license GNU General Public License version 2 or later; see LICENSE.txt */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class MouseOverZoomRender
{
	protected static function strip_html_tags_textarea( $text )
	{
		$pattern1=array('@<textarea[^>]*?>.*?</textarea>@siu');
		$pattern2=array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',"$0", "$0", "$0", "$0", "$0", "$0","$0", "$0");

	    $text = preg_replace($pattern1,$pattern2, $text );

		return $text ;
	}

	function ApplyPlugin(&$text_original, &$jscode, $jquerylibrarylink, $checkwindowsize, $avoidtextarea, $applytoclass, $defaultzoomfactor,
						 $bigimagepostfix,$triggerevent,$method,$defaul_degree)
	{
		if($defaultzoomfactor<1 or $defaultzoomfactor>10)
			$defaultzoomfactor=2;

		if($avoidtextarea)
			$text=MouseOverZoomRender::strip_html_tags_textarea($text_original);
		else
			$text=$text_original;

		$imagelist=array();

		$foundcount=0;

		MouseOverZoomRender::ApplyImageClass($text, $text_original, $jscode, $jquerylibrarylink, $checkwindowsize, $applytoclass, $defaultzoomfactor,$bigimagepostfix,$triggerevent,$imagelist, $foundcount,$defaul_degree);
		MouseOverZoomRender::ApplyBracketMode($text, $text_original, $jscode, $jquerylibrarylink, $checkwindowsize, $defaultzoomfactor,$bigimagepostfix,$triggerevent,$imagelist, $foundcount,$defaul_degree);

		if($foundcount>0)
		{
			$jscode=MouseOverZoomRender::getJSCode($jquerylibrarylink,$imagelist, $triggerevent,$checkwindowsize,$defaul_degree);
		}
	}






	protected static function getMOZCode($src, $bigimagepostfix, $triggerevent, $defaultzoomfactor,&$imagelist,$width,$height, $zoomfactor, $img_alt,$img_title,$defaultrotatedegree)
	{

		$src_arr=MouseOverZoomRender::csv_explode(',',$src,'"',false);



					if($zoomfactor==0)
					{
						if(isset($src_arr[4]))
						{
							if((float)$src_arr[4]>1)
								$zoomfactor=(float)$src_arr[4];
							else
								$zoomfactor=$defaultzoomfactor;
						}
						else
							$zoomfactor=$defaultzoomfactor;
					}


					if(isset($src_arr[5]) and $src_arr[5]!='')
						$rotatedegree=(int)$src_arr[5];
					else
						$rotatedegree=$defaultrotatedegree;

					if(count($src_arr)>1)
					{

						$smallimage=$src_arr[0];

						if(isset($src_arr[1]))
							$bigimage=$src_arr[1];
						else
							$bigimage=$src;

						if(isset($src_arr[2]))
						{
							$aLink=$src_arr[2];
							$aLink=str_replace('http:/','http://',$aLink);
							$aLink=str_replace('https:/','https://',$aLink);
						}
						else
							$aLink='';

						if(isset($src_arr[3]))
							$link_target=$src_arr[3];
						else
							$link_target='';

					}
					else
					{
						$smallimage=$src;
						$bigimage=$src;
						$aLink='';
						$link_target='';
					}

					if($bigimagepostfix!='' and ($bigimage==$smallimage or $bigimage==''))
						$bigimage=str_replace('.',$bigimagepostfix.'.',$smallimage);

					if($triggerevent=="moc")
						$aLink='';



					//check the image

					$isImageOk=false;

					$sz=MouseOverZoomRender::getImageSize($smallimage,$width,$height);
					if(count($sz)==0)
					{
						//image not found or corrupted or external and there is no permissions to get it's info

						}
					else
					{
						//Big Image
						if($bigimage!='')
						{
								$sz_big=MouseOverZoomRender::getImageSize($bigimage,0,0);
							if(count($sz_big)==0)
								$bigimage=$smallimage;
						}
						else
							$bigimage=$smallimage;

						$isImageOk=true;
					}


					if($isImageOk)
					{

						//Apply Mouse Over to this Image

						$padding=0;

						$classname='';
						$isNew=true;
						foreach($imagelist as $img)
						{
							// $img[0] - classname=$img[0];
							// $img[1] - width=$img[1];
							// $img[2] - height=$img[2];
							// $img[3] - small image file name
							// $img[4] - big image file name
							// $img[5] - zoomfactor_=$img[5];
							// $img[9] - rotate_degree_=$img[9];
							//if($img[3]==$smallimage and $img[4]==$bigimage and $img[5]==$zoomfactor )
							//{
								//$isNew=false;
								//$classname=$img[0];
								//break;
							//}
						}

						if($isNew)
						{
							$classname.='moz_thumb_'.count($imagelist);
							$imagelist[]=array($classname, $sz[0],$sz[1],$smallimage,$bigimage,$zoomfactor,0,0,0,$rotatedegree);

						}

						if($img_title=='')
							$img_title=$img_alt;
						else
							if($img_alt=='')
								$img_alt=$img_title;

						$gcss='min-width:auto!important;max-width:auto!important;'
							.'min-height:auto!important;max-height:auto!important;'
							.'padding:0;margin:0;border:0;width:'.$sz[0].'px;height:'.$sz[1].'px;';

						$newtag=''
						.'<div class="MouseOverZoomPlugin" id="'.$classname.'_id" style="position: relative; width: '.$sz[0].'px; height: '.$sz[1].'px;">'
						.'<img src="'.$smallimage.'" id="'.$classname.'_small" alt="'.$img_alt.'" title="'.$img_title.'" style="visibility:visible;'.$gcss.'" width="'.$sz[0].'" height="'.$sz[1].'" />'
						.'<div class="'.$classname.'"'
						.' style="position: absolute; top: 0; left: 0; width: '.($sz[0]+$padding*$zoomfactor).'px; height: '.($sz[1]+$padding*$zoomfactor).'px;'
						.($triggerevent=="moc" ? 'cursor:pointer;' : '')
						.'">';





						$img2tag='<img src="'.$bigimage.'" id="'.$classname.'_big" width="'.$sz[0].'" height="'.$sz[1].'" alt="'.$img_alt.'" title="'.$img_title.'"'
							.' style="z-index:0;visibility:hidden;'.$gcss
							.($triggerevent=="moc" ? 'cursor:pointer;' : '')
							.'" />';

						if($triggerevent=='moc')
						{
							$newtag.=$img2tag;
						}
						else
						{
							if($aLink!='')
								$newtag.='<a href="'.$aLink.'"'.($link_target!='' ? ' target="'.$link_target.'" ' : '').'>'.$img2tag.'</a>';
							else
								$newtag.='<a>'.$img2tag.'</a>';
						}

						$newtag.='</div></div>';


					}//if($isImageOk)
					else
					{
						$newtag='';
					}

		return $newtag;
	}







	function ApplyBracketMode($text, &$text_original, &$jscode, $jquerylibrarylink, $checkwindowsize, $defaultzoomfactor,$bigimagepostfix,$triggerevent,&$imagelist, &$foundcount,$defaul_degree)
	{
		$result='';

		$options=array();

		//{moz=image_small,image_big,link,link_traget,zoom_factor,WIDTH,HEIGHT,IMG_ALT,IMG_TITLE,rotate_degree}
		$fList=MouseOverZoomRender::getListToReplace('moz',$options,$text,'{}','=');



		$i=0;
		foreach($fList as $f)
		{
			if($options[$i]!='')
			{
				$pair=MouseOverZoomRender::csv_explode(',',$options[$i],'"',false);

				if(isset($pair[5]))
					$width=(int)$pair[5];
				else
					$width=0;

				if(isset($pair[6]))
					$height=(int)$pair[6];
				else
					$height=0;

				if(isset($pair[7]))
					$img_alt=$pair[7];
				else
					$img_alt='';

				if(isset($pair[8]))
					$img_title=$pair[8];
				else
					$img_title='';

				if(isset($pair[9]) and $pair[9]!='')
					$rotate_degree=(int)$pair[9];
				else
					$rotate_degree=$defaul_degree;

				$newtag=MouseOverZoomRender::getMOZCode($options[$i], $bigimagepostfix, $triggerevent, $defaultzoomfactor, $imagelist, $width,$height,0,$img_alt,$img_title,$rotate_degree);



				$text_original=str_replace($fList[$i],$newtag,$text_original);
				$foundcount++;
			}
			$i++;
		}



		return true;
	}





	protected static function ApplyImageClass($text, &$text_original, &$jscode, $jquerylibrarylink, $checkwindowsize, $applytoclass, $defaultzoomfactor,$bigimagepostfix,$triggerevent,&$imagelist, &$foundcount,$defaul_degree)
	{
		if($applytoclass=='')
			$applytoclass='mouseoverzoom';

		$foundcount=0;

		$pl=0;


		do
		{

			if(!function_exists("stripos"))
			{
				$p=strpos($text,'<img',$pl);
				if($p===false)
					$p=strpos($text,'<IMG',$pl);
			}
			else
				$p=stripos($text,'<img',$pl);


			if(!($p===false))
			{

				$pe=strpos($text,'>',$p);
				if(!($pe==false))
				{

					$tag=substr($text,$p,$pe-$p+1);
					$classname=MouseOverZoomRender::getAttribute('class', $tag);

					if($classname==$applytoclass)
					{
						$src=MouseOverZoomRender::getAttribute('src', $tag);
						$width=(int)MouseOverZoomRender::getAttribute('width', $tag);
						$height=(int)MouseOverZoomRender::getAttribute('height', $tag);
						$zoomfactor=(float)MouseOverZoomRender::getAttribute('zoom', $tag);
						$img_alt=MouseOverZoomRender::getAttribute('alt', $tag);
						$img_title=MouseOverZoomRender::getAttribute('title', $tag);

						$newtag=MouseOverZoomRender::getMOZCode($src, $bigimagepostfix, $triggerevent, $defaultzoomfactor, $imagelist, $width,$height,$zoomfactor,$img_alt,$img_title,$defaul_degree);
						if($newtag!='')
						{
							$foundcount++;
							$text_original=str_replace($tag,$newtag,$text_original);
						}


					}//if($classname=='mouseoverzoom')

				}//if(!($pe==false))

			}//if(!($p===false))
			$pl=$p+4;
		}
		while(!($p===false));

		return true;
	}

	protected static function getJSCode($jquerylibrarylink, &$imagelist, $triggerevent,$checkwindowsize,$degree)
	{
		$CodeToGo='';

		if($jquerylibrarylink=='')
			$jquerylibrarylink=	'http://code.jquery.com/jquery.min.js';

		if($jquerylibrarylink!='-')
		{
			$CodeToGo='
			<script type="text/javascript" src="'.$jquerylibrarylink.'"></script>
			<script type="text/javascript" src="'.JURI::root(true).'/plugins/system/mouseoverzoom/mouseoverzoom/mouseoverzoom.js"></script>
			';
		}

		$CodeToGo.='

		<style>
			.MouseOverZoomPlugin img {width: none !important;height: none !important;min-height: none !important;max-height: none !important;min-width: none !important;max-width: none !important;}
		</style>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function(){
		';

		foreach($imagelist as $img)
		{
			$classname=$img[0];
			$width=(int)$img[1];
			$height=(int)$img[2];

			$zoomfactor_=(float)$img[5];

			if(isset($img[9]) and $img[9]!='')
				$degree=$img[9];


			//Larger thumbnail preview

			if($triggerevent=='moc')
			{
				$CodeToGo.='
				var '.$classname.'_State=0;
				jQuery("#'.$classname.'_id").click(function(){
					'.$classname.'_State=MOZDoTheJob(this,"'.$checkwindowsize.'","'.$classname.'",'.$classname.'_State,"'.$triggerevent.'",'.(int)$width.','.(int)$height.','.(float)$zoomfactor_.','.(int)$degree.');
				});
			';
			}//if($triggerevent=='moc')
			else
			{
				$CodeToGo.='
				var '.$classname.'_State=0;
				jQuery("div.'.$classname.'").hover(
					function() {
						MOZDoTheJob(this,"'.$checkwindowsize.'","'.$classname.'",'.$classname.'_State,"'.$triggerevent.'",'.(int)$width.','.(int)$height.','.(float)$zoomfactor_.','.(int)$degree.');
					},
					function() {
						MOZUndoTheJob(this,"'.$classname.'",'.$width.','.$height.','.$degree.');
					}
					);
			';
			}//if($triggerevent=='moc')
		}//foreach

	$CodeToGo.='
		});
		</script>
		';
		return $CodeToGo;
	}


	protected static function getImageSize($smallimage,$width,$height)
	{
		if($smallimage=='')
			return array();


		$sz=array();
		if(!(strpos($smallimage,'http')===false))
		{
			//external
			echo '<!-- ';
			$sz=@getimagesize($smallimage);
			echo ' -->';

			if($sz[0]<1 and $sz[1]<1)
			{
				//image not found or not permited to get external image info
				if($width<1 or $height<1)
				{
					//cannot handle unknow dimension image

					return array();
				}
			}

		}
		else
		{
			//local

			if(!file_exists($smallimage))
			{
				if($smallimage[0]=='/')
				{
					$smallimage=substr($smallimage,1);
					if(!file_exists($smallimage))
						return array(); //image not found
				}
				else
					return array(); //image not found
			}

			$sz=getimagesize($smallimage);
			if($sz[0]<1 and $sz[1]<1)
			{
				//image is corrupted
				return array();
			}

		}



		$customwidth=$width;
		if($customwidth>0)
		{

			$customheight=$height;
			if($customheight==0)
				$customheight=$customwidth*$sz[0]/$sz[1];

			$sz[0]=$customwidth;
			$sz[1]=$customheight;
		}
		else
		{
			$customheight=$height;
			if($customheight>0)
			{

				$customwidth=$width;
				if($customwidth==0)
					$customwidth=$customheight*$sz[1]/$sz[0];


				$sz[0]=$customwidth;
				$sz[1]=$customheight;
			}
		}//if($customwidth>0)

		return $sz;

	}



	protected static function getAttribute($attrib, $tag){
	  //get attribute from html tag
		//$re = '/'.$attrib.'=["\']?([^"\' ]*)["\' ]/is';// - without whitespace
		$re = '/'.$attrib.' *= *["\']?([^"\']*)["\' ]/is';// - with whitespace

		preg_match($re, $tag, $match);
		if($match){
			return urldecode($match[1]);
		}else {
			return false;
		}
	}

	public static function getListToReplace($par,&$options,&$text,$qtype,$separator=':',$quote_char='"')
	{
		$fList=array();
		$l=strlen($par)+2;

		$offset=0;
		do{
			if($offset>=strlen($text))
				break;

			$ps=strpos($text, $qtype[0].$par.$separator, $offset);
			if($ps===false)
				break;


			if($ps+$l>=strlen($text))
				break;

			$quote_open=false;

			$ps1=$ps+$l;
			$count=0;
			do{

				$count++;
				if($count>100)
					die;

				if($quote_char=='')
					$peq=false;
				else
				{
					do
					{
						$peq=strpos($text, $quote_char, $ps1);

						if($peq>0 and $text[$peq-1]=='\\')
						{
							// ignore quote in this case
							$ps1++;

						}
						else
							break;

					}while(1==1);
				}

				$pe=strpos($text, $qtype[1], $ps1);

				if($pe===false)
					break;

				if($peq!==false and $peq<$pe)
				{
					//quote before the end character

					if(!$quote_open)
						$quote_open=true;
					else
						$quote_open=false;

					$ps1=$peq+1;
				}
				else
				{
					if(!$quote_open)
						break;

					$ps1=$pe+1;

				}
			}while(1==1);



		if($pe===false)
			break;

		$notestr=substr($text,$ps,$pe-$ps+1);

			$options[]=trim(substr($text,$ps+$l,$pe-$ps-$l));
			$fList[]=$notestr;


		$offset=$ps+$l;


		}while(!($pe===false));

		//for these with no parameters
		$ps=strpos($text, $qtype[0].$par.$qtype[1]);
		if(!($ps===false))
		{
			$options[]='';
			$fList[]=$qtype[0].$par.$qtype[1];
		}

		return $fList;
	}

	public static function csv_explode($delim=',', $str, $enclose='"', $preserve=false)
	{
		$resArr = array();
		$n = 0;
		$expEncArr = explode($enclose, $str);
		foreach($expEncArr as $EncItem)
		{
			if($n++%2){
				array_push($resArr, array_pop($resArr) . ($preserve?$enclose:'') . $EncItem.($preserve?$enclose:''));
			}else{
				$expDelArr = explode($delim, $EncItem);
				array_push($resArr, array_pop($resArr) . array_shift($expDelArr));
			    $resArr = array_merge($resArr, $expDelArr);
			}
		}
	return $resArr;
	}

}
