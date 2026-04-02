<?php 

class Signup
{

	private $error = "";

	public function evaluate($data)
	{

		foreach ($data as $key => $value) {
			# code...

			if(empty($value))
			{
				$this->error = $this->error . $key . " is empty!<br>";
			}

			if($key == "email")
			{
				if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$value)) {
        
 					$this->error = $this->error . "invalid email address!<br>";
    			}
			}

			if($key == "first_name")
			{
				if (is_numeric($value)) {
        
 					$this->error = $this->error . "first name cant be a number<br>";
    			}

    			if (strstr($value, " ")) {
        
 					$this->error = $this->error . "first name cant have spaces<br>";
    			}
 
			}

			if($key == "last_name")
			{
				if (is_numeric($value)) {
        
 					$this->error = $this->error . "last name cant be a number<br>";
    			}

    			if (strstr($value, " ")) {
        
 					$this->error = $this->error . "last name cant have spaces<br>";
    			}

			}
  
			
		}

		// Validate passwords match
		if(isset($data['password']) && isset($data['password2'])){
			if($data['password'] !== $data['password2']){
				$this->error .= "Passwords do not match!<br>";
			}
			if(strlen($data['password']) < 6){
				$this->error .= "Password must be at least 6 characters!<br>";
			}
		}

		if($this->error != ""){ return $this->error; }

		$DB = new Database();

		//check tag name
		$base_tag = preg_replace('/[^a-z0-9_]/i', '', strtolower(($data['first_name'] ?? '') . ($data['last_name'] ?? '')));
		if($base_tag == ""){
			$base_tag = "user";
		}
		$data['tag_name'] = $base_tag;
		$data['email'] = $data['email'];

		$sql = "select id from users where tag_name = ? limit 1";
		$check = $DB->read_prepared($sql, "s", [$data['tag_name']]);
		while(is_array($check)){

			$data['tag_name'] = $base_tag . rand(0,9999);
			$sql = "select id from users where tag_name = ? limit 1";
			$check = $DB->read_prepared($sql, "s", [$data['tag_name']]);
		}

		$data['userid'] = $this->create_userid();
		//check userid
		$sql = "select id from users where userid = ? limit 1";
		$check = $DB->read_prepared($sql, "i", [$data['userid']]);
		while(is_array($check)){

			$data['userid'] = $this->create_userid();
			$sql = "select id from users where userid = ? limit 1";
			$check = $DB->read_prepared($sql, "i", [$data['userid']]);
		}

		//check email
		$sql = "select id from users where email = ? limit 1";
		$check = $DB->read_prepared($sql, "s", [$data['email']]);
		if(is_array($check)){

			 $this->error = $this->error . "Another user is already using that email<br>";
		}
 

		if($this->error == "")
		{

			//no error
			$this->create_user($data);
		}else
		{
			return $this->error;
		}
	}

	public function create_user($data)
	{

		$DB = new Database();

		$first_name = ucfirst($data['first_name']);
		$last_name = ucfirst($data['last_name']);
		$gender = $data['gender'];
		$email = $data['email'];
		$password = $data['password'];
		$userid = $data['userid'];
		$tag_name = $data['tag_name'];

		$password = hash("sha1", $password);
		
		//create these
		$url_address = strtolower($data['first_name']) . "." . strtolower($data['last_name']);
		$url_address = preg_replace('/[^a-z0-9\.]/i', '', $url_address);

		$query = "insert into users 
		(userid,first_name,last_name,gender,email,password,url_address,tag_name) 
		values 
		(?,?,?,?,?,?,?,?)";

		$DB->save_prepared($query, "isssssss", [$userid, $first_name, $last_name, $gender, $email, $password, $url_address, $tag_name]);
	}
 
	private function create_userid()
	{

		$length = rand(4,19);
		$number = "";
		for ($i=0; $i < $length; $i++) { 
			# code...
			$new_rand = rand(0,9);

			$number = $number . $new_rand;
		}

		return $number;
	}
}