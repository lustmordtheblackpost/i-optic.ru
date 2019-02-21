<?php

if( !defined( "in_ochki" ) && !defined( "in_ochki_admin" ) ) die( "You can't access this file directly" );

class MySQL
{
	//var $sql_host		= '127.0.0.1';
	//var $sql_user		= 'root';
	//var $sql_pass		= '';
	//var $main_database	= 'shop_real';
	
	var $sql_host		= 'localhost';
	var $sql_user		= 'shop';
	var $sql_pass		= 'Gfhjkm123';	
	var $main_database	= 'shop_real';

	var $last_error 		= "";
	var $t_prefix		= 'shop_';	
	var $main_dbcnx 	= null;
	
	var $settings = array();
	
	function init()
	{	
		$this->main_dbcnx = @mysql_connect( $this->sql_host, $this->sql_user, $this->sql_pass );
		if( !$this->main_dbcnx )
			return false;

		@mysql_query ("set character_set_client='utf8'");
		@mysql_query ("set character_set_results='utf8'");
		@mysql_query ("set collation_connection='utf8_general_ci'");

		if( !@mysql_select_db( $this->main_database, $this->main_dbcnx ) )
			return false;
			
		$a = $this->mqm( "SELECT * FROM `".$this->t_prefix."settings` WHERE 1" );
		while( $r = @mysql_fetch_assoc( $a ) ) {
			$this->settings[$r['name']] = $r['value'];
			$this->settings[$r['name']."_comment"] = $r['comment'];
		}
			
		return true;
	}
	
	function mq( $q )
	{
		if( !$q )
			return null;

		$a = @mysql_query( $q." LIMIT 1", $this->main_dbcnx );
		
		return @mysql_num_rows( $a ) ? @mysql_fetch_array( $a ) : $this->setLastError();
	}
	
	function mq_spec( $from, $where = "1", $what = "*", $order = "", $dir = "ASC" )
	{
		$a = @mysql_query( "SELECT ".$what." FROM `".$from."` WHERE ".$where.( $order ? " ORDER BY ".$order." ".$dir : "" )." LIMIT 1", $this->main_dbcnx );
		
		return @mysql_num_rows( $a ) ? @mysql_fetch_array( $a ) : $this->setLastError();
	}

	function mqm( $qm )
	{
		if( !$qm )
			return null;

		$a = @mysql_query( $qm, $this->main_dbcnx );

		return @mysql_num_rows( $a ) ? $a : $this->setLastError();
	}
	
	function mqm_spec( $from, $where = "1", $what = "*", $order = "", $dir = "ASC", $limit = "" )
	{
		$a = @mysql_query( "SELECT ".$what." FROM `".$from."` WHERE ".$where.( $order ? " ORDER BY ".$order." ".$dir : "" ).( $limit ? " LIMIT ".$limit : "" ), $this->main_dbcnx );

		return @mysql_num_rows( $a ) ? $a : $this->setLastError();
	}

	function mu( $u )
	{
		if( !$u )
			return -1;
			
		@mysql_query( $u, $this->main_dbcnx );
		$this->setLastError();
		
		return $this->last_error ? -1 : 1;
	}
	
	function getTableRecordsCount( $table, $where = "1" )
	{
		$c = @mysql_fetch_array( @mysql_query( "SELECT count(*) FROM $table WHERE $where" ) );
		$this->setLastError();
		return isset( $c[0] ) ? $c[0] : 0;
	}
	
	function setLastError()
	{
		$this->last_error = @mysql_error();
		
		return null;
	}
}

?>