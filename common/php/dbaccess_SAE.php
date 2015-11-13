<?php
//require "log.php";
define('MYSQL',1);
class DB {
	private $serverName; // 数据库服务器名称
	private $connectionInfo; // 数据库连接信息
	public $conn; // 数据库连接资源
	private $sql; // sql语句
	private $result; // sql语句执行结果
	                 // 构造函数
	public function __construct($serverName = "localhost", $connectionInfo = array("Database"=>"wx","CharacterSet"=>"UTF-8")) {
		$this->serverName = $serverName;
		$this->connectionInfo = $connectionInfo;
		$this->connect (); // 实例化时即连接数据库
	}
	// 连接数据库函数
	private function connect() {
		  if(MYSQL){
			  $link=@mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
			  $this->conn = $link;
			  if($link)
		      {
				mysql_select_db(SAE_MYSQL_DB,$link);
				mysql_query("SET NAMES UTF8");
			  }else{
				echo "dbaccess:mysql connection error";
				die();
			  }
		  }else{
			$this->conn = sqlsrv_connect ( $this->serverName, $this->connectionInfo ) or die (  print_r( sqlsrv_errors(), true) );
			sqlsrv_query ( $this->conn, "SET NAMES 'UTF8'" ); // 统一utf8编码格式
		  }
	}
	// 执行sql语句
	protected function query($sql) {
		
		if(MYSQL){
			$result = mysql_query($sql);
			if ($result) {
			   $this->result = $result;
		    }else{
			   $this->result = null;
			}
		}else{
		! empty ( $sql ) ? $this->sql = $sql : die ( "The sql can not be empty!" );
		$result = sqlsrv_query ( $this->conn, $this->sql );
		if ($result) {
			$this->result = $result;
		} /* else {
			echo $sql;
			die ( "The sql is error!" );
		} */
		}
	}
	// 执行结果
	public function execsql($sql) {
		$sqlType = strtoupper ( substr ( trim ( $sql ), 0, 6 ) ); // 抓取sql语句类型
		if ($sqlType == "SELECT") {
			$this->query ( $sql ); // 执行sql语句
			$rows = $this->getRows ();
			return $rows;
		} else {
			if ($sqlType == "INSERT" || $sqlType == "UPDATE" || $sqlType == "DELETE") {
				$this->query ( $sql );
				return $this->result;
			}
		}
	}
	// 查询多条结果
	protected function getRows() {
		// $row = sqlsrv_fetch_array ( $this->result, SQLSRV_FETCH_ASSOC );
		$rows = array();
		if(MYSQL){
			if($this->result != null){
				while ( $row = mysql_fetch_assoc ( $this->result ) ) {
						$rows [] = $row; 
				}
			}
			
		}else{
			if($this->result != null){
				while ( $row = sqlsrv_fetch_array ( $this->result, SQLSRV_FETCH_ASSOC ) ) {
						$rows [] = $row; 
				}
			}
		}
		return $rows;
	}
	//查询一条结果
	public function getrow($sql){
		$this->query ( $sql ); // 执行sql语句
		if(MYSQL){
			$row = mysql_fetch_assoc ( $this->result);
		}else
		  $row = sqlsrv_fetch_array ( $this->result, SQLSRV_FETCH_ASSOC );
		return $row;
	}
	protected function get_num_row($sql) {
		$row = array ();
		$this->query ( $sql );
		if($this->result != null)
			$row = mysql_fetch_array ( $this->result, MYSQL_NUM );
		return $row;
	}
	public function insert($table, $array) {
		$keys = join ( ",", array_keys ( $array ) ); // 获取传过来的键名
		$vals = "'" . join ( "','", array_values ( $array ) ) . "'"; // 获取传过来的值
		$sql = "INSERT INTO {$table}({$keys}) VALUES ({$vals})";
		//echo $sql;
		$this->query ( $sql );
		return $this->result;
	}
	public function db_close(){
		if($result)
			mysql_free_result($result);
		mysql_close();
	}
}
