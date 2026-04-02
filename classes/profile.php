<?php

Class Profile
{
	
	function get_profile($id){

		$DB = new Database();
		$query = "select * from users where userid = ? limit 1";
		return $DB->read_prepared($query, "i", [$id]);

	}
}