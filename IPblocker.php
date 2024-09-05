<?php
 
 
# get correct id for plugin
$thisfile=basename(__FILE__, ".php");
 
# register plugin
register_plugin(
	$thisfile, //Plugin id
	'IPblocker', 	//Plugin name
	'1.0', 		//Plugin version
	'multicolor',  //Plugin author
	'https://ko-fi.com/multicolorplugins', //author website
	'Block Dangerous IP Access on yours website based on abuseipdb', //Plugin description
	'plugins', //page type - on which admin tab to display
	'IPblockerSettings'  //main function (administration)
);
 
add_action('index-pretemplate','ipblockFrontend' );


function ipblockFrontend(){

	$api_key = ''; // Zamień na swój klucz API

 $user_ip = $_SERVER['REMOTE_ADDR'];

 $api_url = "https://api.abuseipdb.com/api/v2/check?ipAddress={$user_ip}";

 $ch = curl_init($api_url);

 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Key: $api_key",
    "Accept: application/json"
]);

 $response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

 if ($response === false) {
    die('Nie udało się połączyć z API AbuseIPDB: ' . curl_error($ch));
}

 curl_close($ch);

 $data = json_decode($response, true);

if ($http_code === 200 && isset($data['data'])) {
    $abuse_data = $data['data'];

     if ($abuse_data['isPublic'] && $abuse_data['abuseConfidenceScore'] > 0) {
        die('Your IP address is blocked due to reports. Contact your administrator.
');
    }
} else {
    die('Failed to process response from AbuseIPDB API.
');
};


}

 
# add a link in the admin tab 'theme'
add_action('plugins-sidebar','createSideMenu',array($thisfile,'IPblocker Settings'));
 
 
 
function IPblockerSettings() {

	$fileapi = @file_get_contents(GSDATAOTHERPATH.'ipblocker.txt');
	
	echo "
	
<h3>IPblocker</h3>
<p>Get API from <a href='https://www.abuseipdb.com' target='_blank'>abuseipdb.com</a></p>
<form method='POST'>
<input type='text' value='".$fileapi."' name='api' style='width:100%;padding:10px;'>
<input type='submit' name='submit' value='save API' style='padding:10px 15px;margin-top:10px;background:#000;color:#fff;margin-bottom:10px;border:none;'>	
</form>
	<script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Support Me on Ko-fi', '#29abe0', 'I3I2RHQZS');kofiwidget2.draw();</script>

	";


if(isset($_POST['submit'])){

	file_put_contents(GSDATAOTHERPATH.'ipblocker.txt',$_POST['api']);

	echo '<meta http-equiv="refresh" content="0">';

}

}
?>