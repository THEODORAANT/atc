<?php
    include(__DIR__.'/../bootstrap/config.php');
    include(__DIR__.'/../bootstrap/bootstrap.php');
    include(__DIR__.'/CLIStringFormatter.class.php');
    ATC::bootstrap(true, trim(php_uname("n")));



    $Conf = Conf::fetch();
    $Conf->cli = true;
    
    $Page = Page::fetch();
    $Page->is_admin = true;
    
	$CLI = new CLIStringFormatter();   