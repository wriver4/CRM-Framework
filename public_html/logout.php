<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

@setcookie( session_name(), "", time()-3600, '/');
@session_unset();
@session_destroy();
header('Location: login');
die();
