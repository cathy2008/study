<?php
/**
*微信端获取用户的openid、昵称和头像
*/
header("content-type:text/html;charset=utf-8"); 
function getData($url) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
	curl_setopt ( $ch, CURLOPT_ENCODING, 'gzip' );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
	$data = curl_exec ( $ch );
	curl_close ( $ch );
	return $data;
}

$appId = "wx185042e75357a05c";
$appSecret = "475a5c12426ab538da833b334bc0f540";
if(isset($_COOKIE['openid'])){
	$openid=$_COOKIE['openid'];
	$nickname=$_COOKIE['nickname'];
	$headimgurl=$_COOKIE['headimgurl'];
	//echo "read from cookie:".$openid;
}else{
    //用户同意授权，获取code
    $code=$_GET['code'];
    //通过code换取网页授权access_token
    $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appId."&secret=".$appSecret."&code=".$code."&grant_type=authorization_code";
    $res=getData($url);
    $result=json_decode($res,true);
    $openId=$result['openid'];
    $access_token=$result['access_token'];
    //拉取用户信息 
    $url2="https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openId."&lang=zh_CN";
    // echo $url2;die;
    $res2=getData($url2);
    $result2=json_decode($res2,true);
    // var_dump($result2);die;
    $openid=$result2['openid'];
	$nickname=$result2['nickname'];
	$headimgurl=$result2['headimgurl'];
    setcookie('openid',$openid,time()+3600*24*2);
    setcookie('nickname',$nickname,time()+3600*24*2);
    setcookie('headimgurl',$headimgurl,time()+3600*24*2);

}

