<?php
header("content-type:text/html;charset=utf-8");
class ZAN{
	public $table;//点赞存放的数据库表
	private $first="wx_";
	public $dbId;//点赞对象的infoId（文章Id）|activityId（活动Id）
	public $is_zan;
	private $dbIdName;//$table中点赞对象的$dbId的字段。infoId（文章Id）|activityId（活动Id）
	public $zanNum=0;//点赞对象的评论次数
	/*
	 * @$table:点赞存放的数据库表
	 */
	public function __construct($table){
		$this->table=$table;
	}
	
	//判断点赞对象是否可点赞，并为dbIdName赋值
	public function isZan($dbId){
		global $db;
		//echo "89";die;
		$this->dbId=$dbId;
		if ($this->table==$this->first."zan"){
			/*
			 * 对文章评论，从数据库字段中判断是否可评论
			 */
			//判断该文章是否可点赞
			$sql_info_is = "select is_zan from wx_info where id='{$this->dbId}'";
			$res_info_is = $db->getrow ( $sql_info_is );
// 			echo $sql_info_is;
			$this->is_zan=$res_info_is['is_zan'];
			$this->dbIdName='infoId';
		}elseif ($this->table==$this->first."activity_zan") {
			/*
			 * 对活动评论，默认为可评论
			 */
			$this->is_zan=1;
			$this->dbIdName='activityId';
		}
		return $this->is_zan;
	}
	
	/*
	 * 获取评论对象的点赞次数
	 * @$dbId:点赞对象的infoId（文章Id）|activityId（活动Id）
	 */
	public function zanNum($dbId){
		global $db; 
		$this->isZan($dbId);
		$result=array();
// 		echo $this->is_zan;die;
		if ($this->is_zan){
			$sql_leaveword_num= "select id from " .$this->table." where ".$this->dbIdName."='{$this->dbId}' ";
			$res_leaveword_num=$db->execsql($sql_leaveword_num);
			$this->zanNum=count($res_leaveword_num);
		}else {
			$this->zanNum=0;
		}
		return $this->zanNum;
	}
	
	/*
	 * 用户对点赞对象增加点赞
	 * @$dbId:点赞对象的infoId（文章Id）|activityId（活动Id）
	 */
	public function zanAdd($dbId,$openid){
		global $db;
		global $regex; 
		$this->isZan($dbId);
		$zandata=array();
		if($this->is_zan){
			if ($regex->isNumber($this->dbId)){ 
				$zandata['userId']=$openid;
				$zandata[$this->dbIdName]=$this->dbId;
				$sql_is_zan = "select id from ".$this->table." where ".$this->dbIdName."='{$this->dbId}' and userId='{$zandata['userId']}'";
				$res_is_zan = $db->getrow ( $sql_is_zan );
// 				echo $sql_is_zan;die;
				if (! empty ( $res_is_zan )) {
					return 2; // 你已赞过该文章信息
				} else {
					$zandata ['date'] = date ( 'Y-m-d H:i:s', time () );
					$insert = $db->insert ( $this->table, $zandata );
					if ($insert) {
						return 1; // 点赞成功
					} else {
						return 0; // 点赞失败
					}
				}	
			}else{
				return 0;
			}
		}
	}
}