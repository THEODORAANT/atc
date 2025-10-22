#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $old_db = 'dbPerchwww';
    $atc_db = 'dbATC';


    $DB = Factory::get('DB');

    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblAddonDownloads');

    // Get data
    $sql = 'SELECT ad.downloadDateTime, ad.userID AS customerID, a.addonSlug FROM '.$old_db.'.tblAddonDownloads ad, '.$old_db.'.tblAddons a
            WHERE ad.addonID=a.addonID';
    $rows = $DB->get_rows($sql);

    if (Util::count($rows)) {
    	foreach($rows as $row) {
            $DB->insert($atc_db.'.tblAddonDownloads', $row);
    	}
    }


    echo Console::output();