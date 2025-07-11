<?php

require ('../system/DatabaseConnector.php');
if (user_is_logged_in()) {
    redirect(PROOT . 'app/');
} else {
    redirect(PROOT. 'auth/signout');
}
