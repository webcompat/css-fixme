<?php
/*

This is obviously just a quick and very dirty hack to make it easier to share CSS:fixme analysis of live CSS files out there in the wild.
It would probably be better to port the JS code to Python or something and do the whole analysis / fixing server-side -
but that's a bigger project..

*/
$url = isset($_GET['url']) ? $_GET['url'] : '';
$html = file_get_contents('cssfixme.htm');
$html = str_replace('if you find problems.', 'if you find problems.<br><form>Analyze URL (CSS files only): <input type="url" name="url" value="'.htmlentities($url).'"><input type="submit"></form>', $html);
if($url && substr($url, 0, 4) == 'http'){
    $url_contents = get_url($url);
    $is_css_content = false;
    foreach($url_contents[1] as $key => $value){
        if(preg_match("/text\/css/", $url_contents[1]['content_type'])){
            $is_css_content = true;
            break;
        }
    }
    if(!$is_css_content){
        echo 'ERROR: URL didn\'t return text/css Content-Type header, bailing out for security reasons';
        print_r($url_contents);
        exit;
    }

    $html = str_replace('<textarea>', '<textarea>'.$url_contents[0], $html) . '<script>doTheBigStyleFixing(document.getElementsByTagName(\'textarea\')[0].value)</script>';
    $html = str_replace('Paste CSS in this box', '<b>Below is the code from ' . htmlentities($url) . '</b><br>Paste CSS in this box', $html);
}
echo $html;


// code below from http://de.php.net/manual/de/ref.curl.php comments

/*==================================
Get url content and response headers (given a url, follows all redirections on it and returned content and response headers of final url)

@return    array[0]    content
        array[1]    array of response headers
==================================*/
function get_url( $url,  $javascript_loop = 0, $timeout = 5 )
{
    $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

    $cookie = tempnam ("/tmp", "CURLCOOKIE");
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_ENCODING, "" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
    curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
    $content = curl_exec( $ch );
    $response = curl_getinfo( $ch );
    curl_close ( $ch );

    if ($response['http_code'] == 301 || $response['http_code'] == 302)
    {
        ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");

        if ( $headers = get_headers($response['url']) )
        {
            foreach( $headers as $value )
            {
                if ( substr( strtolower($value), 0, 9 ) == "location:" )
                    return get_url( trim( substr( $value, 9, strlen($value) ) ) );
            }
        }
    }


    return array( $content, $response );
}


?>
