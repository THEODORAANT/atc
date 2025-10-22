#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $DB = DB::fetch();

    $email_file = trim(file_get_contents('beta_emails.txt'));
    $emails     = explode("\n", $email_file);

    if (Util::count($emails)) {

        $Customers       = Factory::get('Customers');
        $Licenses        = Factory::get('Licenses');
        $Products        = Factory::get('Products');
        $ProductVersions = Factory::get('ProductVersions');
        
        $Product         = $Products->find(4); // Runway
        $ProductVersion  = $ProductVersions->get_latest_for_beta($Product->id());


        foreach($emails as $email) {
            $Customer = $Customers->get_one_by('customerEmail', trim($email));

            if ($Customer) {
                $License = $Licenses->create($Product, $ProductVersion, $Customer->id(), false);

                if ($License) {
                    $License->update(['licenseDesc'=>'Perch Runway beta test']);
                    echo $CLI->s('Done: '.$email, 'light_green');
                }
            }else{
                echo $CLI->s('Customer not found:: '.$email, 'black', 'red');
            }
        }
    }