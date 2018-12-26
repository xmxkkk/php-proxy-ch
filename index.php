<?php
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

function url_handle($url){
    if(strpos($url, "//") === 0){
        return "http:".$url;
    }else if(strpos($url, "http://") !== 0&&strpos($url, "https://") !== 0){
        return "http://".$url;
    }
    return $url;
}

$url="https://www.zhihu.com/";
if(isset($_GET['url'])){
    $url=urldecode($_GET['url']);
    $url=url_handle($url);
}

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
// curl_setopt($ch,CURLOPT_POST,1);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);

$header_end_pos=strpos($output,"\r\n\r\n");
$header=substr($output,0,$header_end_pos);
$content=substr($output,$header_end_pos+4);

$dom=new Dom;
$dom->load($content);

$strs=["a"=>"href","link"=>"href","img"=>"src"];

foreach ($strs as $k=>$v){
    $idx=0;
    while(true){
        $element=$dom->find($k,$idx++);
        if($element){
            $oldurl=$element->getAttribute($v);
            $element=$element->setAttribute($v,'http://localhost:8080/index.php?url='.urlencode($oldurl));
        }else{
            break;
        }
    }
}

$headers=explode("\r\n",$header);
for($i=0;$i<count($headers);$i++){
    if(stripos($headers[$i],"content-length") === 0){
        continue;
    }
    header($headers[$i]);
}

$content=strval($dom);

header("Content-length: ".strval(strlen($content)));
echo $content;