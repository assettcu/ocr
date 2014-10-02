<?php
// Theme name from Jquery UI themes
$theme = "bluebird";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/help.css" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js" type="text/javascript"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript"></script>

	<script src="https://compass.colorado.edu/libraries/javascript/jquery/modules/cookie/jquery.cookie.js" type="text/javascript"></script>
	<link rel="stylesheet" href="https://compass.colorado.edu/libraries/javascript/jquery/jquery-ui/themes/<?=$theme?>/jquery-ui.css" type="text/css" />
	
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-7054410-7']);
        _gaq.push(['_trackPageview']);
        
        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    
    	
    	// Button Script for all buttons
    	jQuery(document).ready(function($){
    		$("button").button();
    	});
	</script>
</head>

<body>

<div class="container" id="page">

	<a href="<?php echo Yii::app()->baseUrl; ?>"><div id="header"></div></a><!-- header -->
	<style>
	    #login {
	        width:400px;
	        float:right;
	        text-align:right;
	        padding:5px;
	    }
	</style>
	<div id="login">
	    Welcome, <?php echo Yii::app()->user->name; ?> - 
	    <?php if(Yii::app()->user->isGuest): ?>
	    <a href="<?php echo Yii::app()->createUrl('index'); ?>">login</a>
	    <?php else: ?>
	    <a href="<?php echo Yii::app()->createUrl('logout'); ?>">logout</a>
	    <?php endif; ?> - 
        <a href="<?php echo Yii::app()->createUrl('instructions'); ?>">instructions</a> - 
        <a href="<?php echo Yii::app()->createUrl('index'); ?>">home</a>
	</div>
	<br/>
	
	<?php echo $content; ?>

	<div class="clear"></div>

	<div id="footer">
    	<div id="assett-logo">
			<a href="http://assett.colorado.edu/"><span>ASSETT Website</span></a>
        </div>
        <div id="footer-links" style="text-align:right;">
			<a href="http://www.colorado.edu/">University of Colorado Boulder</a><br/>
			<a href="http://www.colorado.edu/legal-trademarks-0">Legal &amp; Trademark</a> | <a href="http://www.colorado.edu/legal-trademarks-0">Privacy</a> <br/>
			<a href="https://www.cu.edu/regents/">&copy; <?php echo date('Y'); ?> Regents of the University of Colorado</a>
        </div>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>
