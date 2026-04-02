<?php

class Login
{

	private $error = "";
 
	public function evaluate($data)
	{

		$email = $data['email'];
		$password = $data['password'];

		$DB = new Database();
		$query = "SELECT * FROM users WHERE email = ? LIMIT 1";
		$result = $DB->read_prepared($query, "s", [$email]);

		if($result)
		{

			$row = $result[0];

			if($this->hash_text($password) == $row['password'])
			{

				//create session data
				$_SESSION['mybook_userid'] = $row['userid'];

			}else
			{
				$this->error .= "wrong email or password<br>";
			}
		}else
		{

			$this->error .= "wrong email or password<br>";
		}

		return $this->error;
		
	}

	private function hash_text($text){

		$text = hash("sha1", $text);
		return $text;
	}

	public function check_login($id,$redirect = true)
	{
		if(is_numeric($id))
		{

			$DB = new Database();
			$query = "SELECT * FROM users WHERE userid = ? LIMIT 1";
			$result = $DB->read_prepared($query, "i", [$id]);

			if($result)
			{

				$user_data = $result[0];
				return $user_data;
			}else
			{
				if($redirect){
					header("Location: login.php");
					die;
				}else{

					$_SESSION['mybook_userid'] = 0;
				}
			}
 
			 
		}else
		{
			if($redirect){
				header("Location: login.php");
				die;
			}else{
				$_SESSION['mybook_userid'] = 0;
			}
		}

	}
 
}