#!/usr/bin/env php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

   // include(__DIR__.'/../env.php');


 $DB			= array(
    							'host'		=> 'localhost'
    						  );
    	        $DB['host']   = 'perchwww.edgeof.ms'; //78.47.187.112
                          $DB['socket'] = '/Applications/MAMP/tmp/mysql/mysql.sock';
                          $DB['user']   = 'perch_web';
                          $DB['pass']   = 'any87leaf';
                          $DB['db']     = 'dbATC';

$url="http://localhost:3001/sendmail/affiliatecampaign";
 $args=array();
 //   $DB = DB::fetch();

  //  $sql = 'SELECT customerFirstName ,customerLastName ,customerEmail FROM dbATC.tblCustomers where customerActive =1 and customerEmail not like "%edgeofmyseat.com"';
     $sql = 'SELECT customerFirstName ,customerLastName ,customerEmail FROM dbATC.tblCustomers where customerEmail  like "%theodora%"';

   // $customers = $DB->get_rows($sql);
  //  $connection = mysqli_connect( $DB['host'],   $DB['user'],  $DB['pass'], $DB['db'] ); // Establishing Connection with Server
   // $db = mysqli_select_db("dbATC", $connection); // Selecting Database
    //MySQL Query to read data
   // $customers = mysql_query( $sql, $connection);
   // echo "customers";
  //  print_r($customers);
  // Open the CSV file
  $sendfile = fopen('sendlive.csv', 'r');

  // Initialize an empty array
  $array = [];

  // Read each line and parse it into an array
  while (($data = fgetcsv($sendfile)) !== false) {
      $array[] = $data[2];
  }

  // Close the file
  fclose($sendfile);

  // Output the resulting array
  print_r($array);
$file = fopen("customerslive.csv","r");
$handle = fopen("sendlive4.csv", "a");

while(! feof($file))
  {
   $customer=fgetcsv($file);
 // print_r($customer);
    $args['firtsname'] =$customer[0];
                      	$args['lastname'] = $customer[1];
                      		$args['email'] =  $customer[2];
                      		echo "args";
                      		print_r( json_encode($args));
                      		$email =$args['email'];
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                              $emailErr = "Invalid email format";
                              echo   $emailErr ;
                              continue;
                            }else{
	echo "else";
if (!in_array($email, $array)) {


    $ch = curl_init();echo "curl_init";
              curl_setopt($ch, CURLOPT_URL, $url); echo $url;
               curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
              //curl_setopt($ch, CURLOPT_USERAGENT, 'ATC');
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);echo "CURLOPT_RETURNTRANSFER";
              curl_setopt($ch, CURLOPT_POST, true);echo "CURLOPT_POST";
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));echo "CURLOPT_POSTFIELDS";
              try {
              echo "result";
                $result = curl_exec($ch);	 print_r( $result);
                fputcsv($handle, ($args));
              }

              //catch exception
              catch(Exception $e) {
                echo 'Message: ' .$e->getMessage();
              }

              $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 echo "Return code is {$httpCode} \n" .curl_error($ch);
            if ( $httpCode != 200 ){
                echo "Return code is {$httpCode} \n" .curl_error($ch);
            } else {
                echo "<pre>".htmlspecialchars($result)."</pre>";
            }

              print_r($result);
              curl_close($ch);
              }
              }

  sleep(3);
  }
fclose($file);
fclose($handle);
   /* if (Util::count($customers)) {
        foreach($customers as $customer) {

        $args['firtsname'] =$customer["customerFirstName"];
                    	$args['lastname'] = $customer["customerLastName"];
                    		$args['email'] =  $customer["customerEmail"];
                    		print_r( $args);
   $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
             curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            //curl_setopt($ch, CURLOPT_USERAGENT, 'ATC');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

          if ( $httpCode != 200 ){
              echo "Return code is {$httpCode} \n"
                  .curl_error($ch);
          } else {
              echo "<pre>".htmlspecialchars($result)."</pre>";
          }

            print_r($result);
            curl_close($ch);

sleep(10);

           // echo $result ? json_decode($result, true) : false;
        }

     }*/

     ?>
