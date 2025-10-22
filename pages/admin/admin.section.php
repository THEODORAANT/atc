<?php
    if (!$Auth->logged_in() || $AuthenticatedUser->userType()!='Admin') {
        Util::redirect($Conf->auth_url);
    }

    $Page->title = 'Admin';
