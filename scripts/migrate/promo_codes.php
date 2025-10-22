#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $old_db = 'dbPerchwww';
    $atc_db = 'dbATC';


    $DB = Factory::get('DB');

    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblPromoCodes');


    // Get customer
    $sql = 'SELECT * FROM '.$old_db.'.tblPromotions';
    $rows = $DB->get_rows($sql);

    if (Util::count($rows)) {
    	foreach($rows as $promo) {
            $DB->insert($atc_db.'.tblPromoCodes', $promo);
    	}
    }


    echo Console::output();