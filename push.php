<?php

//echo exec("git clone git@bitbucket.org:LiteByte/cmz-backend.git staging 2>&1");

echo '<pre>';
passthru("sh push.sh 2>&1");
echo '</pre>';
