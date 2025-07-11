<?php 

	// DELETE user profile picture

    require ('../system/DatabaseConnector.php');

	if (isset($_POST['tempuploded_file_id'])) {

		$tempuploded_img_id_filePath = BASEURL . $_POST['tempuploded_file_id'];

		$unlink = unlink($tempuploded_img_id_filePath);
		if ($unlink) {
			$sql = "
				UPDATE levina_users 
				SET user_profile = ? 
				WHERE user_id = ?
			";
			$statement = $dbConnection->prepare($sql);
			$result = $statement->execute([NULL, $user_data['user_id']]);
			if (isset($result)) {
				
				$message = "deleted profile picture";
                // add_to_log($message, $user_data['user_id']);

				echo '';
			}
		}
	}
