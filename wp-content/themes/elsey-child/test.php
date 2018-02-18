<?php
# No need for the template engine
define( 'WP_USE_THEMES', false );
# Load WordPress Core
// Assuming we're in a subdir: "~/wp-content/plugins/current_dir"
require_once( '../../../wp-load.php' );

global $wpdb;


$post_status = implode("','", array('wc-on-hold') );

$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts 
            WHERE post_type = 'shop_order'
            AND post_status IN ('{$post_status}')
        ");

echo "<pre>";

//var_dump($result);

//japan holyays
$ggh=japan_holiday();
asort($ggh);

foreach($result as $rs){
	$oid= $rs->ID;
	$order = new WC_Order( $oid );
	if('bacs' != $order->get_payment_method()){
		continue;
	}
	
	echo '<br/>Order ID: ';
	echo $oid;	
	echo '<br/>';	
	echo 'Payment Method: ';
    echo $order->get_payment_method();
	echo '<br/>';
	echo 'Order Date: ';
	echo date('Y-m-d',strtotime($rs->post_date));
	echo '<br/>';
	echo 'Count Start Date: ';
	echo $orderdate=date('Y-m-d',strtotime($rs->post_date.' +1 day'));
	echo '<br/>';
	
	
	
$date1 = $orderdate;
$date2 = date('Y-m-d');


	
$datePeriod = returnDates($date1, $date2);

$days=0;

foreach($datePeriod as $date) {
	$cdt=$date->format('Y-m-d');
	
	// || 
	if($date->format('D') !='Sun' &&  $date->format('D') !='Sat'){
		if(!in_array($cdt,$ggh)){
		 $days++;
		echo '<strong>'.$days.'</strong> ';
		echo '- '.$date->format('D').' ~ ';
		echo $date->format('Y-m-d').'<br/>';
		
		if($days > 4){
			//break;
		}
		
		}
		else{
		//echo $date->format('D').'<br/>';
		echo '<br/>'.$date->format('D').' ~ '.$date->format('Y-m-d').' ~ Holyday<br/><br/>';
		}
	}
	
	
}
echo 'Days count to today: ';
echo $days;
	echo '<br/>';
	
if($days>4){	
	echo '<strong style="color: #f00;">Should Update Order status to: wc-unpaid-deadline</strong><br/>';
	//$order->update_status('wc-unpaid-deadline', 'Auto Update Status :'.date('Y-m-d'));
}
else{
	echo '<strong style="color: green;">No Need to Update Order Status</strong><br/>';
}
	
	
	
	}
















    function japan_holiday() {
        $url = 'https://www.googleapis.com/calendar/v3/calendars/en.japanese%23holiday%40group.v.calendar.google.com/events?key=AIzaSyCn0d_Io6LAlzzaX_UzeFnQzGBTQxmkwW4';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        if (!empty($result)) {
            $json = json_decode($result);
			
			
			$i=0;
                $datas = array();
				
				foreach($json->items as $mydata)

    {

		  $datas[]=$mydata->start->date;
    }        
				
	
                return $datas;

        }
    }
	
	

	
	
	









function returnDates($fromdate, $todate) {
    $fromdate = \DateTime::createFromFormat('Y-m-d', $fromdate);
    $todate = \DateTime::createFromFormat('Y-m-d', $todate);
    return new \DatePeriod(
        $fromdate,
        new \DateInterval('P1D'),
        $todate->modify('+1 day')
    );
}

	echo '<pre>';
		var_dump($ggh);
	