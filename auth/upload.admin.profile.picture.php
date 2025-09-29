<?php 

	// Upload admin profile

    require ('../system/DatabaseConnector.php');

	if ($_FILES["file_upload"]["name"]  != '') {

		$test = explode(".", $_FILES["file_upload"]["name"]);

		$extention = end($test);

		$name = md5(microtime()) . '.' . $extention;

		$uploadDir =  '../../assets/media/uploads/admin-media/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

		$name = $uploadDir . $name;

		$location = BASEURL . $name;

		//check if admin dexist
		$move = move_uploaded_file($_FILES["file_upload"]["tmp_name"], $location);
		if ($move) {
			$sql = "
				UPDATE susu_admin 
				SET admin_profile = ?
				WHERE admin_id  = ? 
			";
			$statement = $dbConnection->prepare($sql);
			$result = $statement->execute([$name, $admin_data['admin_id']]);

			if (isset($result)) {
				$log_message = 'Admin [' . $admin_id . '] has updated profile picture!';
                add_to_log($message, $admin_data['admin_id'], 'admin');

				echo '';
			}
		} else {
			echo 'Something went wrong, please try again!';
		}
	}