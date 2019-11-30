<?php

include dirname(__FILE__) .'/engine/autoload.php';

Session::destroySession();

redirect('login.php',$url);
