//https://stackoverflow.com/questions/15191058/css-rotation-cross-browser-with-jquery-animate
    jQuery(function($)
    {
        $.fn.animateRotate = function(from_angle,to_angle, duration, easing, complete) {
          var args = $.speed(duration, easing, complete);
          var step = args.step;
          return this.each(function(i, e) {
            args.complete = $.proxy(args.complete, e);
            args.step = function(now) {
              $.style(e, 'transform', 'rotate(' + now + 'deg)');
              if (step) return step.apply(e, arguments);
            };

            $({deg: from_angle}).animate({deg: to_angle}, args);
          });
        };
    });


        function MOZDoTheJob(obj,checkwindowsize,classname,classState,triggerevent,width,height,zoomfactor,degree)
        {
            //step 1
            if(checkwindowsize!='')
            {
                var cwsp=checkwindowsize.split('x');
                if(cwsp.length==2)
                {
                    var w=parseInt(cwsp[0]);if(isNaN(w))w=0;
                    var h=parseInt(cwsp[1]);if(isNaN(h))h=0;

                    if(MOZclientWidth()<w || MOZclientHeight()<h)
                    	return;
        		}
        	}

            //step 2 - get offsets
            document.getElementById(classname+"_small").style.visibility='hidden';
            document.getElementById(classname+"_big").style.visibility='visible';
            document.getElementById(classname+"_id").style.zIndex='1000';

            var width_big=width*zoomfactor;
            var height_big=height*zoomfactor;

            var HorizontalOffset=MOZfindHorizontalOffset(classname+"_id",width,width_big);
            var VerticalOffset=MOZfindVerticalOffset(classname+"_id",height,height_big);
        	var MarginLeft=-((width_big/2)-width/2)+HorizontalOffset;
            var MarginTop=-((height_big/2)-height/2)+VerticalOffset;

            classState=MOZDoTheJob_JQuery(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree);
            return classState;
        }

        function MOZHideImage(classname)
        {
                setTimeout(function(){
                    document.getElementById(classname+"_id").style.zIndex="0";
                    },200);
                setTimeout(function(){
                    document.getElementById(classname+"_small").style.visibility="visible";
                    },400);
                setTimeout(function(){
                    document.getElementById(classname+"_big").style.visibility="hidden";
                    },400);

        }

        function MOZDoTheJob_JQuery_moc(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree)
        {
            if(classState==0)
            {
                	classState=1;

                    jQuery(obj).find('img')
                        .animate({
                            marginTop: MarginTop+'px', marginLeft: MarginLeft+'px', top: '50%',
                            left: '50%',
                            width: width_big+"px",
                            height: height_big+"px"
                        }, 200)
                        .animateRotate(0,degree,200);
            }
            else
            {
                	classState=0;
                    jQuery(obj).find('img')
                        .animate({
                            marginTop: '0',
                            marginLeft: '0',
                            top: '0',
                            left: '0',
                            width: width+"px",
                            height: height+"px",
                        }, 200)
                        .animateRotate(degree,0,200);

                MOZHideImage(classname);
            }
            return classState;
        }

        function MOZDoTheJob_JQuery_moz(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree)
        {
            jQuery(obj).find('img').addClass("hover")
                        .stop()
                        .animate({
                            marginTop: MarginTop+'px', marginLeft: MarginLeft+'px',
                            top: '50%',
                            left: '50%',
                            width: width_big+"px",
                            height: height_big+"px"
                        }, 200)
                        .animateRotate(0,degree,200);
        }


        function MOZUndoTheJob(obj,classname,width,height,degree)
        {
                jQuery(obj).find('img').removeClass("hover")
                                .animateRotate(degree,0,200)
                                .stop()
                                .animate({
                                    marginTop: '0',
                                    marginLeft: '0',
                                    top: '0',
                                    left: '0',
                                    width: width+"px",
                                    height: height+"px",
                                }, 200);


                MOZHideImage(classname);
        }

        function MOZDoTheJob_JQuery(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree)
        {
            if(triggerevent=='moc')
                classState=MOZDoTheJob_JQuery_moc(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree);
            else
                classState=MOZDoTheJob_JQuery_moz(obj,classname,classState,triggerevent,width,height,width_big,height_big,MarginLeft,MarginTop,degree);

            return classState;
        }


		function MOZfindVerticalOffset(id,height,zoomed_height)
        {
		    var node = document.getElementById(id);
		    var curtop = 0;
		    var curtopscroll = 0;
			var scroll_top = MOZscrollTop();
			var scroll_height = MOZclientHeight();

            var VerticalOffset=0;
		    if (node.offsetParent)
            {
		        do {
		            curtop += node.offsetTop;
		            curtopscroll =0;//+= node.offsetParent ? node.offsetParent.scrollTop : 0;
		        } while (node = node.offsetParent);

				var imaged_y=(curtop - curtopscroll)-scroll_top;
				var zoomed_y=imaged_y-((zoomed_height/2)-height/2);

				if(zoomed_height>scroll_height)
				{
					VerticalOffset=-zoomed_y-(zoomed_height-scroll_height)/2;

				}
				else
				{
					if(zoomed_y<0)
						VerticalOffset=-zoomed_y;

					if(zoomed_y+zoomed_height>scroll_height)
						VerticalOffset=(scroll_height)-(zoomed_y+zoomed_height);
				}
            }
			return VerticalOffset;
		}


		function MOZfindHorizontalOffset(id,width,zoomed_width)
        {
		    var node = document.getElementById(id);
		    var curleft = 0;
		    var curleftscroll = 0;
			var scroll_left = MOZscrollLeft();
			var scroll_width = MOZclientWidth();

            var HorizontalOffset=0;
		    if (node.offsetParent)
            {
		        do {
		            curleft += node.offsetLeft;
		            curleftscroll =0;//+= node.offsetParent ? node.offsetParent.scrollLeft : 0;
		        } while (node = node.offsetParent);

				var imaged_x=(curleft - curleftscroll)-scroll_left;
				var zoomed_x=imaged_x-((zoomed_width/2)-width/2);

				if(zoomed_width>scroll_width)
				{
					HorizontalOffset=-zoomed_x-((zoomed_width-scroll_width)/2);
				}
				else
				{
					if(zoomed_x<0)
						HorizontalOffset=-zoomed_x;

					if(zoomed_x+zoomed_width>scroll_width)
						HorizontalOffset=(scroll_width)-(zoomed_x+zoomed_width);
				}
            }

			return HorizontalOffset;
		}


		function MOZclientHeight()
        {
			return MOZfilterResults (
				window.innerHeight ? window.innerHeight : 0,
				document.documentElement ? document.documentElement.clientHeight : 0,
				document.body ? document.body.clientHeight : 0
			);
		}

		function MOZscrollTop() {
			return MOZfilterResults (
				window.pageYOffset ? window.pageYOffset : 0,
				document.documentElement ? document.documentElement.scrollTop : 0,
				document.body ? document.body.scrollTop : 0
			);
		}

		function MOZclientWidth() {
			return MOZfilterResults (
				window.innerWidth ? window.innerWidth : 0,
				document.documentElement ? document.documentElement.clientWidth : 0,
				document.body ? document.body.clientWidth : 0
			);
		}

		function MOZscrollLeft() {
			return MOZfilterResults (
				window.pageXOffset ? window.pageXOffset : 0,
				document.documentElement ? document.documentElement.scrollLeft : 0,
				document.body ? document.body.scrollLeft : 0
			);
		}

		function MOZfilterResults(n_win, n_docel, n_body) {
			var n_result = n_win ? n_win : 0;
			if (n_docel && (!n_result || (n_result > n_docel)))
				n_result = n_docel;
			return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
		}