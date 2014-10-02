<?php 
return array (
  'components' => 
    array (
        'db'=>array(
            'connectionString' => 'mysql:host=localhost;dbname=c_readify_dev',
            'username'=> 'c_readify_dev',
            'password'=> 'hominized phosphatizations hemathermous trichinizations',
            'autoConnect'=> true,
            'tablePrefix'=> false
        ),
    ),
    'import'=>array(
        'application.models.*',
        'application.models.graphics.*',
        'application.models.system.*',
        'application.components.*',
    ),
); 
?>