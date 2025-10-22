#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $old_db = 'dbPerchwww';
    $atc_db = 'dbATC';


    $DB = Factory::get('DB');

    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblReferralCodes');


    // Get codes
    $sql = 'SELECT * FROM '.$old_db.'.tblReferralCodes';
    $rows = $DB->get_rows($sql);

    if (Util::count($rows)) {
    	foreach($rows as $code) {
            $DB->insert($atc_db.'.tblReferralCodes', $code);
    	}
    }


    echo Console::output();