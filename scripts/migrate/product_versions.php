#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $old_db = 'dbPerchwww';
    $atc_db = 'dbATC';


    $DB = Factory::get('DB');

    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblProductVersions');


    // Get rows
    $sql = 'SELECT * FROM '.$old_db.'.tblPerchVersions';
    $rows = $DB->get_rows($sql);

    if (Util::count($rows)) {
    	foreach($rows as $row) {
            unset($row['versionLink']);
            $row['versionVCSTag'] = $row['versionSvnTag'];
            unset($row['versionSvnTag']);
            $DB->insert($atc_db.'.tblProductVersions', $row);
    	}
    }


    echo Console::output();