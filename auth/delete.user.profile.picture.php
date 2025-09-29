<?php 

	// DELETE user profile picture

    require ('../system/DatabaseConnector.php');

	if (isset($_POST['tempuploded_file_id'])) {

		$tempuploded_img_id_filePath = BASEURL . $_POST['tempuploded_file_id'];

		$unlink = unlink($tempuploded_img_id_filePath);
		if ($unlink) {
			$sql = "
				UPDATE susu_admins 
				SET user_profile = ? 
				WHERE admin_id = ?
			";
			$statement = $dbConnection->prepare($sql);
			$result = $statement->execute([NULL, $admin_data['admin_id']]);
			if (isset($result)) {
				
				$log_message = 'Admin [' . $admin_id . '] has deleted profile picture!';
                add_to_log($message, $user_data['user_id'], 'admin');

				echo '';
			}
		}
	}