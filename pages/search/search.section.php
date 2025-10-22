<?php
    if (!$Auth->logged_in()) {
        Util::redirect($Conf->auth_url);
    }