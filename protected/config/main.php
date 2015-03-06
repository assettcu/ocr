<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
$mainconfig = array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'TextOverlay',

	// preloading 'log' component
	'preload'=>array('log'),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
            'showScriptName'=>false,
			'rules'=>array(
				'<id:\d+>'=>'site/view',
				'<action:\w+>/<id:\d+>'=>'site/<action>',
				'<action:\w+>'=>'site/<action>',
			),
		),
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=c_readify_dev',
			'username'=> 'c_readify_dev',
			'password'=> 'hominized phosphatizations hemathermous trichinizations',
			'autoConnect'=> true,
            'tablePrefix'=> false
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
	),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>array(
        'JQUERY_VERSION'            => '1.11.1',
        'JQUERYUI_VERSION'          => '1.11.1',
        'OCR_FILE_IN'               => "C:/web/OCR/scanin/",
        'OCR_FILE_OUT'              => "C:/web/OCR/scanout/",
        'OCR_FILE_IN_TEMP'			=> ROOT."ocr/files/temp/",
    ),
);


# Function to blend two arrays together
function mergeArray($a,$b)
{
    $args=func_get_args();
    $res=array_shift($args);
    while(!empty($args))
    {
        $next=array_shift($args);
        foreach($next as $k => $v)
        {
            if(is_integer($k))
                isset($res[$k]) ? $res[]=$v : $res[$k]=$v;
            else if(is_array($v) && isset($res[$k]) && is_array($res[$k]))
                $res[$k]=mergeArray($res[$k],$v);
            else
                $res[$k]=$v;
        }
    }
    return $res;
}

# If extended attributes are found, include them in the main configuration details
if(is_file(dirname(__FILE__).'/main-ext.php')) {
    $mainconfig = mergeArray($mainconfig, require(dirname(__FILE__).'/main-ext.php'));
}

# Return the details
return $mainconfig;