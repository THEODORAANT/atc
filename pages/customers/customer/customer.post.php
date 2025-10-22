<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/customers/customer/<?php echo $Customer->id(); ?>">Overview</a></li>
        <li><a href="/customers/top/">Top customers</a></li>
        <li><a href="/customers/top100/">Top 100</a></li>
        <li><a href="/customers/regdevs/">Registered Developers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header"><?php echo $Customer->customerFirstName() .' '.$Customer->customerLastName(); ?></h1>

    <div class="row">
      
      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Customer</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>First name</th><td><?php echo $Customer->customerFirstName(); ?></td></tr>
              <tr><th>Last name</th><td><?php echo $Customer->customerLastName(); ?></td></tr>
              <tr><th>Email</th><td><?php echo $Customer->customerEmail(); ?></td></tr>
              <tr><th>Active</th><td><span class="glyphicon <?php echo $Customer->customerActive() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
              <tr><th>Company</th><td><?php echo $Customer->customerCompany(); ?></td></tr>
              <tr><th>Country</th><td><?php echo $Customer->country_name(); ?></td></tr>
              <tr><th>Street Adr 1</th><td><?php echo $Customer->customerStreetAdr1(); ?></td></tr>
              <tr><th>Street Adr 2</th><td><?php echo $Customer->customerStreetAdr2(); ?></td></tr>
              <tr><th>Locality</th><td><?php echo $Customer->customerLocality(); ?></td></tr>
              <tr><th>Region</th><td><?php echo $Customer->customerRegion(); ?></td></tr>
              <tr><th>Postal Code</th><td><?php echo $Customer->customerPostalCode(); ?></td></tr>
              <tr><th>VAT number</th><td><?php echo $Customer->customerVATnumber(); ?></td></tr>
              <tr><th>VAT number validated</th><td><span class="glyphicon <?php echo $Customer->customerVATnumberValid() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
              <tr><th>Xero Contact ID</th><td><?php echo $Customer->customerXeroContactID(); ?></td></tr>
              <tr><th>Discount</th><td><?php echo $Customer->customerDiscount(); ?></td></tr>
              <tr><th>First Order</th><td><?php echo $Customer->customerFirstOrder(); ?></td></tr>
              <tr><th>Lat</th><td><?php echo $Customer->customerLat(); ?></td></tr>
              <tr><th>Lng</th><td><?php echo $Customer->customerLng(); ?></td></tr>
              <tr><th>MailChimp EUID</th><td><?php echo $Customer->customerMailChimpEUID(); ?></td></tr>
              <tr><th>Drip Subscriber ID</th><td><?php echo $Customer->customerDripID(); ?></td></tr>
              <tr><th>StripeID</th><td><a href="https://dashboard.stripe.com/customers/<?php echo $Customer->customerStripeID(); ?>"><?php echo $Customer->customerStripeID(); ?></a></td></tr>
              <tr><th>Referred By</th><td><?php echo $Customer->customerReferredBy(); ?></td></tr>
              <tr><th>Referral Code</th><td><?php echo $Customer->customerReferralCode(); ?></td></tr>
              <tr><th>Forum Email Notifications</th><td><span class="glyphicon <?php echo $Customer->customerForumEmailNotifications() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
            </table>
          </div>
        </div>


        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Address</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>Needs geocoding</th><td><span class="glyphicon <?php echo $Customer->customerNeedsGeocoding() ? 'glyphicon-ok' : 'glyphicon-remove' ;?>"></span></td></tr>
              <tr><th>Customer to review</th><td><span class="glyphicon <?php echo $Customer->customerToReviewAddress() ? 'glyphicon-ok' : 'glyphicon-remove' ;?>"></span></td></tr>
              <tr><th>Address manually verified</th><td><span class="glyphicon <?php echo $Customer->customerAdrManuallyVerified() ? 'glyphicon-ok' : 'glyphicon-remove' ;?>"></span>
                  <a href="/customers/customer/verify/<?php echo $Customer->id(); ?>">Verify address&hellip;</a>
              </td></tr>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xs-6">

          <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Orders (<?php echo Util::count($orders); ?>)</h3></div>
            <div class="panel-body">
              <?php  
                    if (Util::count($orders)) {
                        echo '<table class="table table-condensed">';

                          echo '<tr>';
                            echo '<th>Invoice #</th>';
                            echo '<th>Date</th>';
                            echo '<th>Amount</th>';
                          echo '</tr>';


                        foreach($orders as $Order) {

                          echo '<tr>';
                          if($Order->orderInvoiceNumber()==null){
                            echo '<td><a style="color:red" href="/orders/order/'.$Order->id().'/">Failed'.$Order->id().'</a></td>';
                          }else{
                           echo '<td><a href="/orders/order/'.$Order->orderInvoiceNumber().'/">'.$Order->orderInvoiceNumber().'</a></td>';
                          }

                            echo '<td>'.date('d M y H:i', strtotime($Order->orderDate())).'</td>';
                            echo '<td>'.$Order->orderValue().' '.$Order->orderCurrency().'</td>';
                          echo '</tr>';
                        }

                        echo '</table>';
                    }
              ?>
            </div>
          </div>

          <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Licenses (<?php echo Util::count($licenses); ?>)</h3></div>
            <div class="panel-body">
              <?php  
                    if (Util::count($licenses)) {
                        echo '<table class="table table-condensed">';

                          echo '<tr>';
                            echo '<th>Date</th>';
                            echo '<th>Product</th>';
                            echo '<th>Domain</th>';
                          echo '</tr>';


                        foreach($licenses as $License) {

                          echo '<tr>';
                            echo '<td><a href="/licenses/license/'.$License->licenseSlug().'/">'.date('d M Y H:i', strtotime($License->licenseDate())).'</a></td>';
                            echo '<td><small>';
                              switch($License->productID()) {
                                case 1:
                                  echo 'Perch';
                                  break;
                                case 4:
                                  echo '<b>Runway</b>';
                                  break;
                                case 10:
                                  echo 'Runway Dev';
                                  break;
                              }
                            echo '</small></td>';
                            if(!$License->licenseActive()){
                              echo '<td class="bg-danger">Inactive</td>';
                            }else{
                              echo '<td>'.$License->licenseDomain1().'</td>';
                            }

                          echo '</tr>';
                        }

                        echo '</table>';
                    }
              ?>
              <div class="pull-right">
                <a href="/customers/customer/add-license/<?php echo $Customer->id(); ?>" class="btn btn-primary btn-sm">Add license</a>
              </div>
            </div>
          </div>


          <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Activity</h3></div>
            <div class="panel-body">
              <?php  
                    if (Util::count($activity)) {
                        echo '<table class="table table-condensed table-hover">';

                          echo '<tr>';
                            echo '<th>Date</th>';
                            echo '<th>Went from</th>';
                            echo '<th>To</th>';
                          echo '</tr>';


                        foreach($activity as $Customer) {

                          echo '<tr>';
                            echo '<td>'.date('d M Y H:i', strtotime($Customer->timestamp())).'</td>';
                            echo '<td>'.$Customer->from_tag().'</td>';
                            echo '<td>'.$Customer->to_tag().'</td>';
                          echo '</tr>';
                        }

                        echo '</table>';
                      
                    }
              ?>
            </div>
          </div>




      </div>
    



    


    </div>
  </div>
</div>
