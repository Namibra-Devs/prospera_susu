<?php 

	// Upload user profile

    require ('../system/DatabaseConnector.php');

	if ($_FILES["file_upload"]["name"]  != '') {

		$test = explode(".", $_FILES["file_upload"]["name"]);

		$extention = end($test);

		$name = md5(microtime()) . '.' . $extention;

		$name = 'assets/media/users-profile/' . $name;

		$location = BASEURL . $name;

		//check if user dexist
		$move = move_uploaded_file($_FILES["file_upload"]["tmp_name"], $location);
		if ($move) {
			$sql = "
				UPDATE levina_users 
				SET user_profile = ?
				WHERE user_id  = ? 
			";
			$statement = $dbConnection->prepare($sql);
			$result = $statement->execute([$name, $user_data['user_id']]);

			if (isset($result)) {
				$message = "updated profile picture";
                // add_to_log($message, $user_data['user_id']);

				echo '';
			}
		} else {
			echo 'Something went wrong, please try again!';
		}
	}
