<?php
header("content-type:text/html;charset=utf-8"); 
class LWD{
	public  $table;//评论存放的数据库表
	private $first="wx_";
	public  $dbId;//评论对象的infoId（文章Id）|activityId（活动Id）
	public  $is_leaveword;
	private $dbIdName;//$table中评论对象的$dbId的字段。infoId（文章Id）|activityId（活动Id）
	public  $lwdNum=0;//评论对象的评论次数
	public  $audit;
	/*
	 * @$table:评论存放的数据库表
	 */
	public function __construct($table){
		$this->table=$table;
		$this->audit();
	}
	
	//审核功能是否开启
	private function audit(){
	    global $db; 
	    $sql_audit="select flag from wx_audit where name='leaveword'";
	    $res_audit=$db->getrow($sql_audit);
	    $this->audit=$res_audit['flag']; 
	}
	
	//对评论进行审核
	public function setAudit($audit,$dbId){
	    global $db;  
    	if($this->audit){
    	    $sql_lwd_audit="update " .$this->table." set audit='{$audit}' where id=".$dbId;
        	$res_lwd_audit=$db->execsql($sql_lwd_audit);
        	if (mysql_affected_rows()>0){
        		return 1;//操作成功
        	}else {
        		return 0;//操作失败
        	}
    	}else{
    	    return 3;//审核功能未开启
    	}
	}
	
	//判断评论对象是否可评论，并为dbIdName赋值
	public function isLwd($dbId){
		$this->dbId=$dbId;
		global $db;
		if ($this->table==$this->first."leaveword"){
			/*
			 * 对文章评论，从数据库字段中判断是否可评论
			 */
			//判断该文章是否可评论
			$sql_info_is = "select is_leaveword from wx_info where id='{$this->dbId}'";
			$res_info_is = $db->getrow ( $sql_info_is );
			$this->is_leaveword=$res_info_is['is_leaveword'];
			$this->dbIdName='infoId';

		}elseif ($this->table==$this->first."activity_leaveword") {
			/*
			 * 对活动评论，默认为可评论
			 */ 
			$this->is_leaveword=1;
			$this->dbIdName='activityId';
		}
		return $this->is_leaveword;
	}
	
	/*
	 * 获取评论对象的评论次数
	 * @$dbId:评论对象的infoId（文章Id）|activityId（活动Id）
	 */
	public function lwdNum($dbId){
		global $db;
		$this->isLwd($dbId); 
		$result=array();
		if ($this->is_leaveword){
		    $sql_leaveword_num= "select id from " .$this->table." where ".$this->dbIdName."='{$this->dbId}' order by date desc";
    		$res_leaveword_num=$db->execsql($sql_leaveword_num);
    		$this->lwdNum=count($res_leaveword_num); 
		}else {
			$this->lwdNum=0;
		}
		return $this->lwdNum;
	}
	
	/*
	 * 分页显示评论对象的评论信息
	 * @$page:评论显示的当前页码
	 * @$num:评论每页显示的行数
	 * @$dbId:评论对象的infoId（文章Id）|activityId（活动Id）
	 */
	public function showLwd($page,$num,$dbId){
		global $db;
		$this->lwdNum($dbId);
		$result=array();
		if ($this->lwdNum>0){ 
			$result['PageNum']=ceil($this->lwdNum/$num);//评论一共有多少页
			$start=($page-1)*$num;//本页显示的起始位置
			$sql_leaveword = "select id,userId,content,date,audit from ".$this->table. " where ".$this->dbIdName."='{$this->dbId}'"." order by date desc  limit ".$start.",".$num;
		    $res_leaveword = $db->execsql ( $sql_leaveword );   
			// $result ['num_leaveword'] = count ( $res_leaveword );// 本页的评论次数
			$result ['num_leaveword'] =$this->lwdNum;// 本页的评论次数
			//如果有人评论，则遍历获取评论内容和评论者的信息
			if ($result ['num_leaveword'] > 0) {
				foreach ( $res_leaveword as $key_leaveword => $val_leaveword ) {
					// 根据userId在wx_user中查询出作者的微信号和微信头像
			        $sql_user_name = "select wechatName, header from wx_user where openId='{$val_leaveword ['userId']}'"; 
					$res_user_name = $db->getrow ( $sql_user_name );  
					$result ['leaveword'][$key_leaveword] ['id'] = $val_leaveword ['id'];
					$result ['leaveword'][$key_leaveword] ['content'] = $val_leaveword ['content'];
					$result ['leaveword'][$key_leaveword] ['date'] = $val_leaveword ['date'];
					$result ['leaveword'][$key_leaveword] ['wechatName'] = $res_user_name ['wechatName'];
					$result ['leaveword'][$key_leaveword] ['header'] = $res_user_name ['header'];
					$result ['leaveword'][$key_leaveword] ['lwd_audit'] = $val_leaveword ['audit'];
				}
			} 
		}else{
			$result['num_leaveword'] = 0;
		}
		$result['require_lwd_audit'] = $this->audit;//是否需要审核
		return $result;
	}
	
	/*
	 * 后台管理员删除某个评论
	 * @$lwdId:评论Id
	 */
	public function delLwd($lwdId){
		global $db;
		global $regex;
		if (empty($lwdId)){
			return 0;//删除失败，请联系技术支持
		}elseif ($regex->isNumber($lwdId)){
			$sql_del_leaveword = "delete from ".$this->table." where Id=".$lwdId;
			$res_del_leaveword = $db->execsql ( $sql_del_leaveword );
			$res = mysql_affected_rows ();
			if ($res>0) {
				return 1; // 删除成功
			} else {
				return 0; // 删除失败，请联系技术支持
			}
		}else {
			return 2;//参数错误
		}
	}
	
	/*
	 * 后台管理员修改某个评论,点击“修改”按钮
	 * @$lwdId:评论Id
	 */
	public function updateLwd($lwdId){
		global $db;
		global $regex;
		if ($regex->isNumber($lwdId)) {
			$sql_leaveword = "select userId,content,date from ".$this->table." where id='{$lwdId}' ";
			$res_leaveword = $db->getrow ( $sql_leaveword ); 
			$sql_user_name = "select wechatName, header from wx_user where openId='{$res_leaveword['userId']}'";
			$res_user_name = $db->getrow ( $sql_user_name ); 
			$leave ['content'] = $res_leaveword ['content'];
			$leave ['date'] = $res_leaveword ['date'];
			$leave ['wechatName'] = $res_user_name ['wechatName'];
			$leave ['header'] = $res_user_name ['header'];
		    return $leave;
		}
	}
	
	/*
	 * 后台管理员修改某个评论,点击“提交”按钮
	 * @$lwdId:评论Id
	 * @$content:修改后的评论内容
	 */
	public function updateLwdOK($lwdId,$content){
		global $db;
		global $regex; 
		if (empty($content)){
			return 2;//请检查空值
		}elseif ($regex->isNumber($lwdId)) {
			$sql_update_leaveword="update ".$this->table." set content='{$content}' where id='{$lwdId}'";
     		$res_update_leaveword=$db->execsql($sql_update_leaveword);
			$res=mysql_affected_rows();
 			if ($res>=0){
				return 1;//修改成功
			}elseif ($res<0) { 
				return 0;//修改失败
			}
		}else{ 
			return 3;//参数错误
		}
	}
}











