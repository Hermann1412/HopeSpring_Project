<?php 

Class Settings
{

	public function get_settings($id)
	{
		$DB = new Database();
		$sql = "select * from users where userid = ? limit 1";
		$row = $DB->read_prepared($sql, "i", [$id]);

		if(is_array($row)){

			return $row[0];
		}
	}

	public function save_settings($data,$id){

		$DB = new Database();

		$password = $data['password'];
		if(strlen($password) < 30){

			if($data['password'] == $data['password2']){
				$data['password'] = hash("sha1", $password);
			}else{

				unset($data['password']);
			}
		}

		unset($data['password2']);

		// Build dynamic update query with prepared statements
		$fields = [];
		$values = [];
		$types = "";

		foreach ($data as $key => $value) {
			// Whitelist allowed fields for security
			$allowed_fields = ['bio', 'password', 'location', 'website', 'phone', 'facebook', 'twitter', 'instagram'];
			if (in_array($key, $allowed_fields)) {
				$fields[] = "$key = ?";
				$values[] = $value;
				$types .= "s";
			}
		}

		if (count($fields) > 0) {
			$sql = "update users set " . implode(", ", $fields) . " where userid = ? limit 1";
			$values[] = $id;
			$types .= "i";
			$DB->save_prepared($sql, $types, $values);
		}
	}
}