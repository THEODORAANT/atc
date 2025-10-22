<?php
	if ($Auth->logged_in()) {
        Util::redirect('/');
    }