<?php
/**
 * 微信第三方开发的多个接口实现
 */
class wechat{
	private $appId;
	private $appSecret;
	private $access_token;
	//
	public function __construct($appId, $appSecret) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->access_token = $this->getAccessToken ();
	}
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
	//
	function postData($url, $data) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$tmpInfo = curl_exec ( $ch );
		if (curl_errno ( $ch )) {
			return curl_error ( $ch );
		}
		curl_close ( $ch );
		return $tmpInfo;
	}
	
	//获取access_token
	function getAccessToken() {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appId . "&secret=" . $this->appSecret;
		$res = $this->getData ( $url );
		$jres = json_decode ( $res, true );
		$access_token = $jres ['access_token'];
		return $access_token;
	}
	
	//上传图文消息内的图片获取media_id
	public function getPicId($filedata){
// 		$url="https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=".$this->access_token;
		$url="http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->access_token."&type=image";
		$res=$this->postData($url, $filedata);
		$result = json_decode ( $res, true );
		$picUrl = $result['media_id'];
		return $picUrl;
	}
	
	//上传图文消息素材
	 public function uploadNews($d){
		$data=json_encode($d);
		$url="https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=".$this->access_token;
		$res=$this->postData($url, $data);
		$result = json_decode ( $res, true );
		return $result;
	}
	
	//群发图文消息
	public function sendNewsToAll($d){
		$data=json_encode($d);
		$url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$this->access_token;
		$res=$this->postData($url, $data);
		echo $res;
		/* $result = json_decode ( $res, true );
		return $result; */
	}
	
	
	//菜单的创建
	public function createMenu($menu){
		$jsonmenu=json_encode($menu);
		$jsonmenu= preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $jsonmenu);
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->access_token;
		$result=$this->postData($url,$jsonmenu);
		var_dump($result);
	}
	
	
	/* private function https_request($url,$data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	} */
	
	//回复多图文类型的微信消息
	public function responseNews($postObj ,$arr){
		$toUser = $postObj->FromUserName;
		$fromUser = $postObj->ToUserName;
		$template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>".count($arr)."</ArticleCount>
					<Articles>";
		foreach($arr as $k=>$v){
			$template .="<item>
						<Title><![CDATA[".$v['title']."]]></Title>
						<Description><![CDATA[".$v['description']."]]></Description>
						<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
						<Url><![CDATA[".$v['url']."]]></Url>
						</item>";
		}
	
		$template .="</Articles>
					</xml> ";
		echo sprintf($template, $toUser, $fromUser, time(), 'news');
	}
	
	// 回复单文本
	public function responseText($postObj,$content){
		$template = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[%s]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>";
		//注意模板中的中括号 不能少 也不能多
		$fromUser = $postObj->ToUserName;
		$toUser   = $postObj->FromUserName;
		$time     = time();
		$msgType  = 'text';
		echo sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
	}
	
	//获取用户关注者列表
	public function getUserInfo() {
		$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->access_token;
		$res = $this->getData ( $url );
		$jres = json_decode ( $res, true );
		// print_r($jres);die;
		$userInfoList = $jres ['data'] ['openid'];
		//print_r($userInfoList) ;
		return $userInfoList;
	}
	
	//用客服接口群发图文消息
	function sendMsgToAll($news) {
		$userInfoList = $this->getUserInfo ();
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $this->access_token;
		foreach ( $news as $val ) {
			$d=json_encode($val);
			$data= preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $d);
// 			echo $data;die;
		$this->postData ( $url, $data );
		}
	}
}