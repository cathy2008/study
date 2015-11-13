<?php
/**
 * $type="showmenu":用于显示微信端的菜单
 * $type="showmenuPC":用于显示PC端左侧的菜单
 * $type="showculture":用于显示企业文化的类别菜单
 * $type="addmenu":用于增加微信端的菜单
 * $type="updatemenu":用于更新微信端的菜单名称
 * $type="updateculture":用于更新企业文化的类别菜单名称
 * $type="deletemenu":用于删除微信端的菜单
 * $type="deleteculture":用于删除企业文化的类别菜单
 */
header("content-type:text/json;charset=utf-8");
require_once './dbaccess.php';
$db=new DB();
/*
 * 根据选择的菜单类型将数据添加到数据库
 * @$menuName:添加的菜单名称
 * @$menuFirstType：选择的菜单一级类型。"articlelist":文章列表；"activity":活动
 * @$menusecondType：选择的菜单二级类型。若一级为文章列表，则"picture":图文；"video":视频；"voice":音频
 *                                若一级为活动，则"vote":投票；"picture":图片互动;"voice":音频互动;"video":视频互动
 * @$menuId:添加菜单的ID
 */
function addmenu($menuName,$menuFirstType,$menusecondType,$menuId){
	$db1=new DB();
	if((!empty($menuFirstType))&&(!empty($menusecondType))){
		/*
		 * 创建菜单后增加功能
		 */
		if ($menuFirstType=='articlelist'){
			/*
			 * 文章列表类型
			 */
			
			switch ($menusecondType){
				case 'picture':
					$mediaType=0;
					break;
				case 'video':
					$mediaType=1;
					break;
				case 'voice':
					$mediaType=2;
					break;
				default:
					break;
			}
			$sql_insert_second="insert into wx_articlelist_module (name,type,menuId,parentId) values ('{$menuName}','{$mediaType}','{$menuId}',0)";
			$res_insert_second=$db1->execsql($sql_insert_second);
			if (mysql_affected_rows()>0){
				return true;//操作成功
			}else {
				return false;//操作失败
			}				
		}elseif ($menuFirstType=='activity'){
			/*
			 * 活动类型:互动
			 */
			switch ($menusecondType){
				case 'vote':
					$reviewTable='wx_vote_project';
					$type=0;
					//判断是否存在投票类型的按钮，若存在，则不能添加
					$sql_isset="select id from wx_activity_module where reviewTable='wx_vote_project'";
					$res_isset=$db1->execsql($sql_isset);
					if (!empty($res_isset)){
						return false;
					}
					break;
				case 'picture':
					$type=1;
					$reviewTable='wx_activity_interact_project';
					break;
				case 'voice':
					$type=2;
					$reviewTable='wx_activity_interact_project';
					break;
				case 'video':
					$type=3;
					$reviewTable='wx_activity_interact_project';
					break;/* 
				case 'review':
					$reviewTable='wx_activity_interact_project';
					break; */
				default:
					break;
			}
		    $sql_insert_second="insert into wx_activity_module (name,reviewTable,menuId,type) values ('{$menuName}','{$reviewTable}','{$menuId}','{$type}')";
// 		    echo json_encode($sql_insert_second);die;
		    $res_insert_second=$db1->execsql($sql_insert_second);
			
			if (mysql_affected_rows()>0){
				return true;//操作成功
			}else {
				return false;//操作失败
			}
		}
	}else {
		/*
		 * 创建菜单后，不增加功能
		 */
		return true;
	}
}

/*
 * 按菜单类型，删除带数据库表的菜单
 * @$menuType:菜单类型
 * @$menuId:菜单的ID
 */
function delmenu($menuType,$menuId) {
	$db=new DB();
	if ($menuType == 1) {
		/*
		 * 若要删除的菜单按钮为活动类型,则活动的模块表为wx_activity_module
		 */
		// 查找出存储活动数据的表
		$sql_table = "select b.id,reviewTable from wx_wechat_module as a left join wx_activity_module as b on a.id=b.menuid where a.id='{$menuId}'";
		$res_table = $db->getrow ( $sql_table );
		if ($res_table ['reviewTable'] == 'wx_activity_interact_project') {
			/*
			 * 图片互动类型的活动
			 */
			$sql_delete_interact = "DELETE a,b,c,d from wx_activity_interact as b left join wx_activity_interact_project as a on a.id=b.projectId
			left join wx_activity_leaveword as c on c.activityId=b.id
			left join wx_activity_zan as d on d.activityId=b.id
			where a.moduleId='{$res_table['id']}'";
			$res_delete_interact = $db->execsql ( $sql_delete_interact );
			if (mysql_affected_rows ()>=0) {
				$error1=1;//删除成功
			} else {
				$error1=0;//删除失败
			}
		} elseif ($res_table ['reviewTable'] == 'wx_vote_project') {
			/*
			 * 投票类型的活动
			 */
			$sql_delete_vote = "delete a,b,c from wx_vote_project as a left join wx_vote_option as b on b.voteId=a.id
					                                                  left join wx_vote_interact as c on c.voteId=a.id";
			$res_delete_vote = $db->execsql ( $sql_delete_vote );
			if (mysql_affected_rows ()>=0) {
				$error1=1;//删除成功
			} else {
				$error1=0;//删除失败
			}
		}
		// 删除菜单数据库表
		$sql_delete_menu = "delete a,b from wx_wechat_module as a left join wx_activity_module as b on a.id=b.menuId where a.id='{$menuId}'";
		$res_delete_menu = $db->execsql ( $sql_delete_menu );
		if (mysql_affected_rows ()>0) {
			$error2 = 1; // 删除成功
		} else {
			$error2 = 0; // 删除失败
		}
		if ($error1 && $error2){
			$del['error']="删除成功";
		} else {
			$del['error']="删除失败";
		}
	} elseif ($menuType == 2) {
		/*
		 * 若要删除的菜单按钮为文章列表类型,则活动的模块表为wx_activity_module
		 */
		// 判断要删除的是否是菜单
		if ($menuId != 0) {
			/*
			 * 是菜单类型
			 */
			// 删除相关文件
			$sql_file = "select thumb,media from wx_info as b left join wx_articlelist_module as a on a.id=b.moduleId";
			$res_file = $db->execsql ( $sql_file );
			foreach ( $res_file as $val_file ) {
				if (! empty ( $val_file ['thumb'] )) {
					unlink ( $val_file ['thumb'] );
				}
				if (! empty ( $val_file ['media'] )) {
					$media = explode ( ';', $val_file ['media'] );
					foreach ( $media as $val_media ) {
						unlink ( $val_media );
					}
				}
			}
			// 删除数据库中的数据
			$sql_delete_articlelist = "DELETE a,b,c,d from wx_info as b left join wx_articlelist_module as a on a.id=b.moduleId
			left join wx_leaveword as c on c.infoId=b.id
			left join wx_zan as d on d.infoId=b.id
			where a.menuId='{$menuId}'";
			$res_delete_articlelist = $db->execsql ( $sql_delete_articlelist );
			if (mysql_affected_rows ()>=0) {
				$error1=1;//删除成功
			} else {
				$error1=0;//删除失败
			}
			// 删除菜单数据库表
			$sql_delete_menu = "delete a,b from wx_wechat_module as a left join wx_articlelist_module as b on a.id=b.menuId where a.id='{$menuId}'";
			$res_delete_menu = $db->execsql ( $sql_delete_menu );
			if (mysql_affected_rows ()>0) {
				$error2 = 1; // 删除成功
			} else {
				$error2 = 0; // 删除失败
			}
			if ($error1 && $error2) {
				$del ['error'] = "删除成功";
			} else {
				$del ['error'] = "删除失败";
			}
		} else {
			$del ['error'] = "不是菜单类型，不能删除";
		}
	} elseif ($menuType == 3) {
		/*
		 * 菜单类型为人才理念
		 */
		$sql_delete_talent2 = "delete from wx_talent_philosophy";
		$sql_delete_talent2 = $db->execsql ( $sql_delete_talent2 );
		if (mysql_affected_rows ()>=0) {
			$errortalent2 = 1; // 删除成功
		} else {
			$errortalent2 = 0; // 删除失败
		}
		// 删除菜单数据库表
		$sql_delete_menu = "delete from wx_wechat_module  where id='{$menuId}'";
		$res_delete_menu = $db->execsql ( $sql_delete_menu );
		if (mysql_affected_rows ()>0) {
			$errortalent1 = 1; // 删除成功
		} else {
			$errortalent1 = 0; // 删除失败
		}
		if ($errortalent1 && $errortalent2 ) {
			$del ['error'] = "删除成功";
		} else {
			$del ['error'] = "删除失败";
		}
	} elseif ($menuType == 4) {
		/*
		 * 菜单类型为人才招聘
		 */
		$sql_delete_talent1 = "delete from wx_talent_recruit";
		$res_delete_talent1 = $db->execsql ( $sql_delete_talent1 );
		if (mysql_affected_rows ()>=0) {
			$errortalent1 = 1; // 删除成功
		} else {
			$errortalent1 = 0; // 删除失败
		}
		// 删除菜单数据库表
		$sql_delete_menu = "delete from wx_wechat_module  where id='{$menuId}'";
		$res_delete_menu = $db->execsql ( $sql_delete_menu );
		if (mysql_affected_rows ()>0) {
			$errortalent2 = 1; // 删除成功
		} else {
			$errortalent2 = 0; // 删除失败
		}
		if ($errortalent1 && $errortalent2 ) {
			$del ['error'] = "删除成功";
		} else {
			$del ['error'] = "删除失败";
		}
	}elseif ($menuType == 5) {
		/*
		 * 菜单类型为企业简介
		 */
		// 删除相关文件
		$sql_file = "select picture from wx_profile";
		$res_file = $db->execsql ( $sql_file );
		foreach ( $res_file as $val_file ) {
			unlink ( $val_file ['picture'] );
		}
		// 删除数据库中的数据
		$sql_delete_profile = "delete from wx_profile";
		$res_delete_profile = $db->execsql ( $sql_delete_profile );
		if (mysql_affected_rows ()>=0) {
			$error1=1; // 删除成功
		} else {
			$error1=0; // 删除失败
		}
		// 删除菜单数据库表
		$sql_delete_menu = "delete from wx_wechat_module  where id='{$menuId}'";
		$res_delete_menu = $db->execsql ( $sql_delete_menu );
		if (mysql_affected_rows ()>0) {
			$error2=1; // 删除成功
		} else {
			$error2=0; // 删除失败
		}
		if ($error1 && $error2) {
			$del ['error'] = "删除成功";
		} else {
			$del ['error'] = "删除失败";
		}
	} elseif ($menuType == 6) {
		/*
		 * 菜单类型为地图导航
		 */
		// 删除数据库中的数据
		$sql_delete_map = "delete from wx_map";
		$res_delete_map = $db->execsql ( $sql_delete_map );
		if (mysql_affected_rows ()>=0) {
			$error1 = 1; // 删除成功
		} else {
			$error1 = 0; // 删除失败
		}
		// 删除菜单数据库表
		$sql_delete_menu = "delete from wx_wechat_module  where id='{$menuId}'";
		$res_delete_menu = $db->execsql ( $sql_delete_menu );
		if (mysql_affected_rows ()>0) {
			$error2 = 1; // 删除成功
		} else {
			$error2 = 0; // 删除失败
		}
		if ($error1 && $error2) {
			$del ['error'] = "删除成功";
		} else {
			$del ['error'] = "删除失败";
		}
	}
	return $del;
}

/*
 * 菜单编辑
 */
$type=$_GET['type'];
// $type="showmenuPC";
if ($type=="showmenu"){
	/*
	 * 将菜单按级别输出名字和ID,用于展示微信端菜单
	 */
	//获取一级模块名称
	$sql_menu="select id,name from wx_wechat_module where parentId=0 order by seq asc";
	$res_menu=$db->execsql($sql_menu);
	//遍历获取二级模块名称
	foreach ($res_menu as $key_First=>$val_First){
		//自身连接查询
		$sql_second="select s.id,s.name from wx_wechat_module as s left join wx_wechat_module as p
				on s.parentId=p.id where p.id='{$val_First['id']}' order by s.seq asc";
		$res_second=$db->execsql($sql_second);
		$res_menu[$key_First]['sub']=$res_second;
	}
// 	print_r($res_menu);
	echo json_encode($res_menu);
}elseif ($type=="showmenuPC"){
	/*
	 * 将菜单按级别输出名字和ID,用于PC端左侧菜单展示
	 */
	//获取一级模块名称
	$sql_menu="select id,name,urlPC,menuType from wx_wechat_module where parentId=0 order by seq asc";
	$res_menu=$db->execsql($sql_menu);
	//遍历获取二级模块名称
	foreach ($res_menu as $key_First=>$val_First){
		//如果是文章列表类型的菜单，则输出moduleId
		$sql_type="select name from wx_wechat_module_type where id='{$val_First['menuType']}'";
		$res_type=$db->getrow($sql_type);
// 		echo $sql_type;die;
		if ($res_type['name']=="文章列表"){
			
			$sql_module="select id from wx_articlelist_module where menuId='{$val_First['id']}'";
			$res_module=$db->getrow($sql_module);
			$res_menu[$key_First]['moduleId']=$res_module['id'];
		}
		//自身连接查询
		$sql_second="select s.id,s.name,s.urlPC,s.parentId,s.menuType from wx_wechat_module as s left join wx_wechat_module as p
		on s.parentId=p.id where p.id='{$val_First['id']}' order by s.seq asc";
		$res_second=$db->execsql($sql_second);
		
		foreach ($res_second as $key_second=>$val_second){
			//如果是文章列表类型的菜单，则输出moduleId
			$sql_type="select name from wx_wechat_module_type where id='{$val_second['menuType']}'";
			$res_type=$db->getrow($sql_type);
			if ($res_type['name']=="文章列表"){
// 				echo $val_second['name'];die;
				$sql_module="select id from wx_articlelist_module where menuId='{$val_second['id']}'";
				$res_module=$db->getrow($sql_module);
				$res_second[$key_second]['moduleId']=$res_module['id'];
			}
			if (($val_second['parentId']!=0)&&($val_second['menuType'])==0){
				$sql_culturelist="select s.id as moduleId,s.name,s.urlPC from wx_articlelist_module as s left join wx_articlelist_module as p
				on s.parentId=p.id where p.menuId='{$val_second['id']}'";
				$res_culturelist=$db->execsql($sql_culturelist);
				$res_second[$key_second]['sub']=$res_culturelist;
			}
			unset($res_second[$key_second]['parentId']);
			unset($res_second[$key_second]['menuType']);
		}
		$res_menu[$key_First]['sub']=$res_second;
		unset($res_menu[$key_First]['menuType']);
	}
		// print_r($res_menu);
	echo json_encode($res_menu);
	
}elseif ($type=="addmenu"){
	$menuName=$_GET['menuName'];//所添加菜单的名称
	$menuFirstType=$_GET['menuFirstType'];//选择的菜单一级类型。"articlelist":文章列表；"activity":活动
	$menusecondType=$_GET['menusecondType'];//选择的菜单二级类型。若一级为文章列表，则"picture":图文；"video":视频；"voice":音频
	                                       // 若一级为活动，则"vote":投票；"picture":图片互动;"voice":音频互动;"video":视频互动
	                                       
	$menuParentId=$_GET['parentId'];//所添加菜单的父类菜单名称，若为0，则表示该菜单为一级菜单
	if ($menuParentId==0){
		/*
		 * 添加一级菜单
		 */
		
		//判断目前一级菜单的个数
		$sql_firstId="select id from wx_wechat_module where parentId=0";
		$res_firstId=$db->execsql($sql_firstId);
		$firstMenuNum=count($res_firstId);
		//若一级菜单的个数不足3个，则添加菜单；若已存在3个，则报错
		if ($firstMenuNum<3){
			//将菜单信息保存在wx_wechat_module表中
			$seq=$firstMenuNum+1;//菜单排序默认为自增一
			
			$firstMenuType=$_GET['firstMenuType'];//一级菜单是空目录还是功能菜单。"nullMenu":空目录；"funcMenu":功能菜单
			if ($firstMenuType=="nullMenu"){
				$menuType=0;
				$urlPC=null;
				$urlWechat=null;
			}elseif ($firstMenuType=="funcMenu"){
				//根据$menuFirstType确定菜单类型
				if ($menuFirstType=="articlelist"){
					$menuTypeName="文章列表";
					$urlPC='../../article/html/articlelist.html';
					$urlWechat=null;
				}elseif ($menuFirstType=="activity"){
					$menuTypeName="活动";
					if ($menusecondType=='vote'){
						$urlPC='../../activity/html/votelist.html';
						$urlWechat=null;
					}elseif ($menusecondType=='picture') {
						$urlPC='./interactlist.html?type=';
						$urlWechat=null;
					}elseif ($menusecondType=='voice') {
						$urlPC=null;
						$urlWechat=null;
					}elseif ($menusecondType=='video') {
						$urlPC=null;
						$urlWechat=null;
					}/* elseif ($menusecondType=='review') {
						$urlPC=null;
						$urlWechat=null;
					} */
				}
				//从wx_wechat_module_type中查找类型ID
				$sql_typeId="select id from wx_wechat_module_type where name='{$menuTypeName}'";
				$res_typeId=$db->getrow($sql_typeId);
				$menuType=$res_typeId['id'];
			}
			
			$sql_insert_first="insert into wx_wechat_module (name,parentId,seq,menuType,urlPC,urlWechat) values ('{$menuName}',0,'{$seq}','{$menuType}','{$urlPC}','{$urlWechat}')";
			$res_insert_first=$db->execsql($sql_insert_first);
			if (mysql_affected_rows()>0){
				/******************菜单添加成功后，根据选项为菜单添加功能************************/
				$menuId=mysql_insert_id();//添加的菜单的moduleId
				//为菜单添加功能
				$secondError=true;
				if ($firstMenuType=="funcMenu"){
					$secondError=addmenu($menuName, $menuFirstType, $menusecondType,$menuId);
				}
				if ($secondError){
					$add['id']=$menuId;//添加的菜单的moduleId
					$add['error']=1;//添加成功
				}else {
					/*****************若菜单功能未添加成功，则删除菜单***********************/
					$add['error']=0;//添加失败
					$sql_del="delete from wx_wechat_module where id='{$menuId}'";
					$res_del=$db->execsql($sql_del);
				}
			}else{
				$add['error']=0;//添加失败
			}
		}else {
			$add['error']=3;//一级菜单最多只能有三个
		}
	}else {
		/*
		 * 添加二级菜单
		 */
		//判断目前一级菜单下二级菜单的个数
		$sql_secondId="select id from wx_wechat_module where parentId='{$menuParentId}'";
		$res_secondId=$db->execsql($sql_secondId);
		$secondMenuNum=count($res_secondId);
		//若二级菜单的个数不足5个，则添加菜单；若已存在5个，则报错
		if ($secondMenuNum < 5) {
			if ($menuFirstType == "articlelist") {
				$menuTypeName = "文章列表";
				$urlPC = '../../article/html/articlelist.html';
				$urlWechat = null;
			} elseif ($menuFirstType == "activity") {
				$menuTypeName = "活动";
				if ($menusecondType == 'vote') {
					$urlPC = null;
					$urlWechat = null;
				} elseif ($menusecondType == 'interact') {
					$urlPC = null;
					$urlWechat = null;
				} elseif ($menusecondType == 'review') {
					$urlPC = null;
					$urlWechat = null;
				}
			}
			//从wx_wechat_module_type中查找类型ID
			$sql_typeId="select id from wx_wechat_module_type where name='{$menuTypeName}'";
			$res_typeId=$db->getrow($sql_typeId);
			$menuType=$res_typeId['id'];
			
			//将菜单信息保存在wx_wechat_module表中
			$seq=$secondMenuNum+1;//菜单排序默认为自增一
			$sql_insert_first="insert into wx_wechat_module (name,parentId,seq,menuType,urlPC,urlWechat) values ('{$menuName}','{$menuParentId}','{$seq}','{$menuType}','{$urlPC}','{$urlWechat}')";
// 			echo json_encode($sql_insert_first);die;
			$res_insert_first=$db->execsql($sql_insert_first);
			
			if (mysql_affected_rows()>0){
				/******************菜单添加成功后，根据选项为菜单添加功能************************/
				$menuId=mysql_insert_id();//添加的菜单的moduleId
				//为菜单添加功能
				$secondError=true;
				$secondError=addmenu($menuName, $menuFirstType, $menusecondType,$menuId);
				if ($secondError){
					$add['id']=$menuId;//添加的菜单的moduleId
					$add['error']=1;//添加成功
				}else {
					/*****************若菜单功能未添加成功，则删除菜单***********************/
					$add['error']=0;//添加失败
					$sql_del="delete from wx_wechat_module where id='{$menuId}'";
					$res_del=$db->execsql($sql_del);
				}
			}else{
				$add['error']=0;//添加失败
			}
		}else {
			$add['error']=2;//二级菜单最多只能有5个
		}
	}
	echo json_encode($add);
}elseif ($type=="updatemenu"){
	$menuId=$_GET['menuId'];//要修改的菜单moduleId
	$menuNewName=$_GET['menuNewName'];//修改之后的菜单名称
	$sql_update="update wx_wechat_module set name='{$menuNewName}' where id='{$menuId}'";
	$res_update=$db->execsql($sql_update);
	if (mysql_affected_rows()>0) {
		echo 1;//更新成功
	}else {
		echo 0;//更新失败
	}
}elseif ($type=='updateculture'){
	$moduleId=$_GET['moduleId'];//要修改的moduleId
	$menuNewName=$_GET['menuNewName'];//修改之后的名称
	$sql_update="update wx_articlelist_module set name='{$menuNewName}' where id='{$moduleId}'";
	$res_update=$db->execsql($sql_update);
	if (mysql_affected_rows()>0){
		echo 1;//更新成功
	}else {
		echo 0;//更新失败
	}
}elseif ($type=='deletemenu'){
// 	echo "909";die;
	$menuId=$_GET['menuId'];//要删除的菜单的moduleId
// 	$menuId=6;
	$menuName=$_GET['menuName'];//要删除的菜单的名称
// 	$menuName="企业文化";
	//核对菜单的moduleId和名称
	$sql_isset="select id,menuType from wx_wechat_module where id='{$menuId}' and name='{$menuName}'";
// 	echo $sql_isset;die;
	$res_isset=$db->getrow($sql_isset);
// 	echo "98";
	if (!empty($res_isset)){
		/*
		 * 菜单存在，进行删除操作
		 */
		//查找该菜单的子菜单是否存在
		$sql_subMenuId="select id,menuType from wx_wechat_module where parentId='{$menuId}'";
// 		echo $sql_subMenuId;die;
		$res_subMenuId=$db->execsql($sql_subMenuId);
		if (empty($res_subMenuId)){
			/*
			 * 没有子菜单，只删除目前菜单的数据库(二级菜单)
			 */
			if ($res_isset['menuType']==0){
				$sql_delete_menu="delete from wx_wechat_module where id='{$menuId}'";
				$res_delete_menu=$db->execsql($sql_delete_menu);
				if (mysql_affected_rows()>0){
					$del['error']= "删除成功";
				}else {
					$del ['error'] = "删除失败";
				}
			}else {
				$del=delmenu($res_isset['menuType'], $menuId);//调用删除功能菜单的函数，返回的是数组，里面存放的是错误代码
			}
		}
		else {
			/*
			 * 有子菜单（一级菜单）
			 */
			//删除子菜单
			foreach ($res_subMenuId as $key_subMenuId=>$val_subMenuId){
				$del2=delmenu($val_subMenuId['menuType'],$val_subMenuId['id']);//调用删除功能菜单的函数，先将子菜单删除
				if ($del2['error']!="删除成功"){
					break;
				}
			}
			// 删除本菜单
			$sql_delete_menu = "delete from wx_wechat_module  where id='{$menuId}'";
			$res_delete_menu = $db->execsql ( $sql_delete_menu );
			if (mysql_affected_rows ()>0) {
				$error2 = 1; // 删除成功
			} else {
				$error2 = 0; // 删除失败
			}
			if ($del2['error'] && $error2) {
				$del ['error'] = "删除成功";
			} else {
				$del ['error'] = "删除失败";
			}
		}
	}else {
		/*
		 * 菜单不存在
		 */
		$del ['error'] = "菜单信息不存在，请联系技术支持";
	}
	echo json_encode($del);
}
