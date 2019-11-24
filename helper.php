
// get the API by the time stamp
add_shortcode('get_parts','simply_get_parts');

function simply_get_parts(){
	PriorityAPI\API::instance()->run();


	$stamp = mktime(0, 0, 0);
	$bod = date(DATE_ATOM,$stamp);

	$url_addition = 'CREATEDDATE ge '.$bod;
    $response = (PriorityAPI\API::instance()->makeRequest('GET','LOGPART?$filter='.urlencode($url_addition),null,true));

    $value = json_decode($response['body'],true)['value'] ;
   if(isset($value[0])) {
	   return  $value[0]['PARTNAME'] ;
   }

	return 'Empty response '.$url_addition;

}
