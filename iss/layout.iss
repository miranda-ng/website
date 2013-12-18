/** {
	box-sizing: border-box;
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
}*/

$width: 950px;

$font: "Arial", "Helvetica", sans-serif;
$fontTitle: /*"CaviarDreamsRegular",*/ $font;
$fontBold: /*"CaviarDreamsBold",*/ $font;
$fontItalic: /*"CaviarDreamsItalic",*/ $font;

@font-face: font("caviardreams-webfont") "CaviarDreamsRegular" normal normal;
@font-face: font("caviardreams_bold-webfont") "CaviarDreamsBold" bold normal;
@font-face: font("caviardreams_italic-webfont") "CaviarDreamsItalic" normal italic;
@font-face: font("caviardreams_bolditalic-webfont") "CaviarDreamsBoldItalic" bold italic;


html, body {
	margin: 0;
	padding: 0;
	font-family: $font;
	font-size: 14px;
}

body {
	background-color: #f3f0e7;
}

#all {
	width: $width;
	
/*	max-width: 1500px;
	min-width: 800px;
	width: 80%;	*/
	
	
	padding: 0;
	margin: 0 auto;
	position: relative;
	display: block;
}

header, footer, article, section, aside {
	display: block;
}

header {
	height: 480px;
	height: 90px;
	position: relative;
	
	#logo {
		display: block;
		background: img("logo_smaller.png") no-repeat left top;
		width: 270px;
		height: 75px;
		float: left;
		margin: 8px 0px 0 15px;
		position: absolute;
		z-index: 10;
		
		text-align: left;
		text-decoration: none;
		color: black;
		font-family: $fontBold;
		font-size: 25px;
		font-weight: bold;
		padding-top: 13px;
		letter-spacing: 1px;
		line-height: 20px;
		//text-shadow: 0px 0px 2px rgba(0, 0, 0, 0.2);
		
		padding: 5px 0 0 160px;
		
		.subtitle {
			font-size: 13px;
			color: #666;
		}
	}
	
	menu {
		list-style: none;
		margin: 0;
		padding: 0;
		z-index: 10;
		position: absolute;
		top: 58px;
		right: 15px;
		margin-right: -2px;
		
		li {
			float: left;
			margin-left: 0px;
			height: 32px;
			overflow: hidden;
			padding: 2px 2px 0 2px;
			
			a {
				color: black;
				font-size: 16px;
				font-family: $font;
				padding: 0 10px;
				display: block;		
				text-align: center;
				line-height: 30px;
				text-decoration: none;
			}
			
			a.active {
				color: white;
			}
			
			a.active, a:hover {
				background: #89d5f5;
				//font-weight:bold;
				@border-radius: 5px 5px 0 0;
				box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.3);
			}
		}
	}
}

section {
	position: relative;
	@self-clear;
	
	@border-radius-simple: 15px;
	
		display: block;
		
		position: relative;
		//top: 90px;
		//padding: 55px 0px 15px;
		//margin-bottom: 100px;
		
		background: white;
		border-top: solid 3px #89d5f5;
		
		box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3);				
		
		
	#top {
		width: 100%;
		height: 180px;
				
		padding: 20px 0px 15px;
		
		background: #F7FCFF;
		
		border-bottom: solid 1px #d6d6d6;
		
		@border-radius: 15px 15px 0 0;
		
		> p {
			clear: left;
			float: left;
			margin-left: 470px;
			margin-top: 0px;
			text-align: left;
			width: 340px;
		}
		
		.download {
			color: black;
			display: block;
			font-family: "Arial", "Helvetica", sans-serif;
			font-size: 42px;
			font-weight: 700;
			margin-left: 460px;
			margin-top: 100px;
			padding-right: 55px;
			position: absolute;
			text-decoration: none;
			width: auto;
			
			img {
				float: left;
				margin-bottom: -29px;
				margin-right: 10px;
				vertical-align: baseline;
			}
			
			&:hover {
				text-decoration: underline;
				span {
					!text-decoration: none;
				}
			}
			
			span {
				display: block;
				clear: both;
				text-align: left;
				margin-left: 76px;
				color: #868686;
				font-size: 22px;
				text-decoration: none;
			}			
		}
		
		&.file_of_week {
			padding-top: 30px;
			height: 280px;
			padding-right: 8px;
		
			.screenshot {
				float: right;
				margin-top: -10px;
				margin-right: 10px;
				img {
					max-width: 240px;
					max-height: 240px;
				}
			}
			
			.download {
				margin-left: 50px;
			}
			
			p {
				clear: left;
				float: left;
				font-size: 14px;
				line-height: 24px;
				margin-bottom: 20px;
				margin-left: 70px;
				margin-top: 105px;
				text-indent: 0px;
				width: 450px;
				height: 72px;
				
				overflow: hidden;
				text-indent: 0px;
				text-overflow: ellipsis;
			}
			
			p + .more {
				float: left;
				margin-left: 430px;
				clear: left;
				cursor: pointer;
				font-family: $fontBold;
				font-weight: bold;
			}
		}
		
		.screenshots {
			height: 170px;
			left: 130px;
			margin-right: 20px;
			position: absolute;
			img {
				height: 100%;
			}
		}
	}
	
	#guy_quote {
		display: block;
		position: absolute;
		background: img("commenter.png") no-repeat left bottom;
		//width: 352px;
		height: 169px;
		//bottom: 30px;
		top: 180px;
		
		p {
			//width: 280px;
			//height: 48px;
			margin-left: 63px;
			margin-top: 5px;
			text-align: center;
			font-size: 17px;
			font-family: $fontItalic;
			font-style: italic;
			background: #e5f3f9;
			background: #F2E5BD;
			background: #FAF2DC;
			background: #FFFCF2;
			padding: 10px 12px;
			@border-radius-simple: 15px;
			@box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.5);
			position: relative;
			
			b {
				font-size: 13px;
				//color: #555;
				font-family: $fontBold;
				font-weight: bold;
				text-align: right;
				display: block;
				padding-top: 3px;
			}
			
			&:after {
				content: "";
				display: block;
				position: absolute;
				z-index: 10;
				width: 31px;
				height: 26px;
				left: 15px;
				bottom: -26px;
				background: img("comment_arrow.png");
			}
		}
	}

	#top + article, #guy_quote + article {
		padding-top: 20px;
	}
	
	article {		
		//float: left;
		width: 560px;
		width: 68%;
		width: 610px;
		clear: both;
		
		display: table-cell;

		padding: 20px 25px 10px;
		
		.news {
			clear: both;
			@self-clear;
			margin-bottom: 20px;
			border-bottom: solid 1px #d6d6d6;
			
			&:last-child {
				border-bottom: none;
			}			
			.more {
				float: right;
				font-family: $fontBold;
			}
			p + .more {
				margin-top: -12px;
				margin-bottom: 18px;
			}			
		}
	}

	aside {
		//padding-top: 50px;
		//float: right;
		width: 250px;
		width: 32%;
		width: 250px;
		padding: 0 20px;
		background: #FAF2DC;
		background: #FFFCF2;
		
		@border-radius: 0px 15px 15px 0;
		
		border-left: solid 1px #d6d6d6;
		
		display: table-cell;
		
		.tweet {
			p {
				font-size: 12px;
				line-height: 22px;
				padding-left: 0px;
				color: #444;
			}
		}
		
		&.addons {
			padding-top: 0px;
			
			menu {
				list-style: none;
				line-height: 22px;
				margin-left: 0px;
				padding-left: 0;
				
				menu {
					list-style: circle;
					margin-left: 20px;
					margin-top: 0;
					margin-bottom: 20px;
				}
			}
		}
	}
	
}

footer {
	padding: 5px 15px 5px 0;
	.copyright {
		font-size: 12px;
		margin-bottom: 10px;
		//font-style: italic;
		text-align: right;
	}
	
}

&.italic {
	font-style: italic;
}
&.bold {
	font-weight: bold;
}

p {
	margin-bottom: 22px;
	font-size: 14px;
	line-height: 24px;
}

.cleaner {
	display: block;
	clear: both;
	visibility: hidden;
	height: 0px;
	padding: 0;
	margin: 0;
}

textarea {
	font-family: $font;
	resize: none;
}

a {
	color: black;
	text-decoration: underline;
	&:hover {
		text-decoration: none;
	}
}

h1, h2, h3, h4, h5 {
	font-family: $fontBold;
	font-weight: bold;
	
	a {
		color: inherit;
		text-decoration: none;
		&:hover {
			text-decoration: none;
		}
	}
}

h1 {
	font-size: 25px;
	margin: 0 0 25px 0;
}

h2 {
	font-size: 20px;
	font-weight: bold;
	//color: #2fb0db;
	margin-bottom: 15px;
}

.date {
	//padding-left: 30px;
	margin: -12px 0 10px 0;
	font-size: 13px;
	color: #515151;
	font-family: $fontBold;
}

h3 {
	//color: #3399cc;
	font-size: 17px;
	font-weight: bold;
	margin-bottom: 10px;
}

ul {
	list-style: disc;
}
ol {
	list-style: decimal;
}

ul, ol {
	margin-left: 25px;
	margin-bottom: 20px;
	li {
		line-height: 22px;
	}
}

p + ul, p + ol {
	margin-top: -15px;
}


hr {
	border: none;
	border-top: solid 1px #d6d6d6;
	margin-bottom: 22px;
	clear: both;
}


h2.twitter {
	background: img("twitter_bird.png") no-repeat right top;
	height: 55px;
	padding-top: 22px;
	border-bottom: solid 1px #d6d6d6;
}

table {
	width: 100%;
	margin-bottom: 22px;

	td, th {
		padding: 5px 5px;
		vertical-align: top;
	}	
}






ul.icons {
	list-style: none;
	margin-left: 0px;
	
	li {
		padding: 0 0 8px 10px;
		
		img {
			vertical-align: middle;
			margin-right: 6px;
			opacity:0.9;
		}
		
		a {
			display: block;
			text-decoration: none;
			line-height: 30px;
			&:hover img {
				opacity: 1;
			}
		}
	}
}

aside {
	h2 {	
		//font-variant: small-caps;
		margin-top: 20px;
		
		border-bottom: solid 1px #d6d6d6;
		padding-bottom: 10px;
	}
	> div {
		clear: both;
		margin-bottom: 25px;
	}
}

#logo {
	//font-variant: small-caps;
}


table tr td:first-child { white-space: nowrap; }


.lastnews {
	dl {
		margin-bottom: 5px;
		line-height: 17px;
	}
	dd {
		margin-left: 78px;
		padding-bottom: 10px;
	}	
	dt {
		float: left;
		width: 70px;
		color: #666;
		text-align: right;
	}
	.more {
		float: right;
		margin-top: 4px;
	}
	@self-clear;
}


.paginator {
	margin: 1em 0;
	text-align: center;
}

.paginator a, .paginator span {
	margin-right: 0.1em;
	padding: 0.2em 0.5em;
	color: #999999;
}

.paginator a {
	border: 1px solid #69838E;
	text-decoration: none;
	color: #69838E;
	&:hover {
		border-color: #47616c;
	}
}

.paginator span.button {
	border: 1px solid #DDDDDD;
}

.paginator .current {
	background: #89D5F5;
	border: 1px solid #69838E;
	color: white;
	font-weight: bold;
}

.aka {
	font-size: 11px;
}


.twocols {
	@columns: 2;
	list-style-position: inside;
	margin-left: 13px;
}
.threecols {
	@columns: 3;
	list-style-position: inside;
	margin-left: 13px;
}
.fivecolumns {
	@columns: 5;
	list-style-position: inside;
	margin-left: 13px;
}

.loginbox {
	margin: 5px 20px 0 0;
	float: right;
	clear: right;
}

.languages {
	margin: -2px 20px 0 0;
	float: right;
	
	a {
		display: inline-block;
		vertical-align: middle;
		text-align: middle;
		padding: 5px;
		border: solid 1px transparent;
		@border-radius-simple: 2px;
		text-decoration: none;
		
		&.active {
			background: white;
			border-color: #aaa;
			@box-shadow: inset 0px 0px 1px #888;
		}
		&:hover {
			border-color: #888;
			text-decoration: none;
		}

		img {
		
		}
	}
}

iframe.facebook {
	border: none;
	border: none;
	overflow: hidden;
	width: 247px;
	height: 258px;
	margin-top: -10px;
}