<?php 

Class Notification extends Database
{

	function get_notifications()
	{
		return $this->read_prepared("select * from users", "", []);
	}
}