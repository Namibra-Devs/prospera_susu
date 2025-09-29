<?php 

	// DELETE admin profile picture

    require ('../system/DatabaseConnector.php');

	if (isset($_POST['tempuploded_file_id'])) {

		$tempuploded_img_id_filePath = BASEURL . $_POST['tempuploded_file_id'];
		$filename = basename($tempuploded_img_id_filePath);
		$filepath = BASEURL . 'assets/media/uploads/collectors-media/' . $filename;
		$unlink = unlink($filepath);
		if ($unlink) {
			$sql = "
				UPDATE susu_admins 
				SET admin_profile = ? 
				WHERE admin_id = ?
			";
			$statement = $dbConnection->prepare($sql);
			$result = $statement->execute([NULL, $admin_data['admin_id']]);
			if (isset($result)) {
				
				$log_message = 'Admin [' . $admin_id . '] has deleted profile picture!';
                add_to_log($log_message, $admin_data['admin_id'], 'admin');

				echo '';
			}
		}
	}