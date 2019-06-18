@gradient-x($left, $right: $left) {
    background-image: -o-linear-gradient(left,$left,$right);
    background-image: -ms-linear-gradient(left,$left,$right);
    background-image: -moz-linear-gradient(left,$left,$right);
    background-image: -webkit-gradient(linear,left top,right top,color-stop(0,$left),color-stop(1,$right));
    $ie: 'progid:DXImageTransform.Microsoft.gradient(startColorStr=' . iergba($left) . ',EndColorStr=' . iergba($right) . ',GradientType=1)';
    %filter: $ie;
    -ms-filter: $ie;
}

@gradient-y($top, $bottom: $top) {
    background-image: -o-linear-gradient(top,$top 100%,$bottom);
    background-image: -ms-linear-gradient(top,$top 100%,$bottom);
    background-image: -moz-linear-gradient(top,$top 100%,$bottom);
    background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0,$top),color-stop(1,$bottom));
    $ie: 'progid:DXImageTransform.Microsoft.gradient(startColorStr=' . iergba($top) . ',EndColorStr=' . iergba($bottom) . ')';
    %filter: $ie;
    -ms-filter: $ie;
}

@transform() {
    -webkit-transform: $_argv;
    -moz-transform: $_argv;
    -ms-transform: $_argv;
    -o-transform: $_argv;
    transform: $_argv;
}

@resize($direction: both) {
    overflow: hidden;
    resize: $direction;
}

@ellipsis() {
    white-space: nowrap;
    overflow: hidden;
    -o-text-overflow: ellipsis;
    text-overflow: ellipsis;
}

@opacity($opacity) {
    -khtml-opacity: $opacity;
    -moz-opacity: $opacity;
    opacity: $opacity;
    $ie: $opacity * 100;
    %filter: 'alpha(opacity=' . $ie . ')';
    -ms-filter: 'progid:DXImageTransform.Microsoft.Alpha(opacity=' . $ie . ')';
}

/*
@background-clip($clip) {
    -webkit-background-clip: $clip;
    -khtml-background-clip: if($clip, border-box, border) if($clip, padding-box, padding);
    -moz-background-clip: if($clip, border-box, border) if($clip, padding-box, padding);
    background-clip: $clip;
}

@background-origin($origin) {
    -webkit-background-origin: $origin;
    -khtml-background-origin: if($origin, border-box, border) if($origin, padding-box, padding) if($origin, content-box, content);
    -moz-background-origin: if($origin, border-box, border) if($origin, padding-box, padding) if($origin, content-box, content);
    background-origin: $origin;
}
*/

@background-size() {
    -webkit-background-size: $_argv;
    -khtml-background-size: $_argv;
    -moz-background-size: $_argv;
    -o-background-size: $_argv;
    background-size: $_argv;
}

@border-image() {
    -webkit-border-image: $_argv;
    -khtml-border-image: $_argv;
    -icab-border-image: $_argv;
    -moz-border-image: $image;
    -o-border-image: $_argv;
    border-image: $_argv;
}

@border-radius-simple($x, $y: $x) {
    -webkit-border-radius: $x $y;
    $radius: raw($x . '/' . $y);
    -khtml-border-radius: $radius;
    -moz-border-radius: $radius;
    border-radius: $radius;
		
		behavior: url('/PIE.htc');
}

@border-radius($topleft, $topright, $bottomright, $bottomleft) {
    border-radius: $topleft $topright $bottomright $bottomleft;
		
		border-top-left-radius: $topleft;
    border-top-right-radius: $topright;
    border-bottom-left-radius: $bottomleft;
    border-bottom-right-radius: $bottomright;
    -moz-border-radius-topleft: $topleft;
    -moz-border-radius-topright: $topright;
    -moz-border-radius-bottomleft: $bottomleft;
    -moz-border-radius-bottomright: $bottomright;
    -webkit-border-top-left-radius: $topleft;
    -webkit-border-top-right-radius: $topright;
    -webkit-border-bottom-left-radius: $bottomleft;
    -webkit-border-bottom-right-radius: $bottomright;
		
		behavior: url('/PIE.htc');
}

@box-shadow() {
    -webkit-box-shadow: $_argv;
    -moz-box-shadow: $_argv;
    box-shadow: $_argv;
		
		behavior: url('/PIE.htc');
}

@box-sizing() {
    -webkit-box-sizing: $_argv;
    -moz-box-sizing: $_argv;
    box-sizing: $_argv;
}

@columns() {
    -webkit-columns: $_argv;
    -moz-columns: $_argv;
    columns: $_argv;
}

@self-clear() {
    zoom: 1#;
    &:after {
        content: '';
        display: block;
        clear: both;
    }
}

@size($width, $height: $width) {
    width: $width * 1px;
    height: $height * 1px;
}

/*
@image($image_path) {
  width: imgWidth($image_path);
  height: imgHeight($image_path);
  background: img($image_path) no-repeat;
}
*/

@hyphens($mode: auto) {
    -webkit-hyphens: $mode;
    -moz-hyphens: $mode;
    -ms-hyphens: $mode;
    hyphens: $mode;
}

@font($size) {
    font-size: $size * 1px;
    font-size: $size * .1rem;
}

@underline($underline: true) {
    if ($underline) {
        text-decoration: underline;
        &:hover {
            text-decoration: none;
        }
    }
    else {
        text-decoration: none;
        &:hover {
            text-decoration: underline;
        }
    }
}

@font-face($_font, $_name: $_font, $_weight: normal, $_style: normal) {
    @font-face {
				font-family: $_name;
        src: url($_font . '.eot');
        src: url($_font . '.eot?#iefix') format('embedded-opentype'),
             url($_font . '.woff') format('woff'),
             url($_font . '.ttf') format('truetype');
        font-weight: $_weight;
        font-style: $_style;
    }
}

@selfclear() {
	&:before, &:after {
		content:"";
		display:table;
	}
	&:after {
		clear:both;
	}
}
