<?php

/*
 * Make sure the following is true before running this script:
 *
 * - the repository exists in the staging folder
 * - the staging branch is checked out
 * - origin's url is in ssh protocol
 *
 */

echo '<pre>';
passthru("sh push.sh 2>&1");
echo '</pre>';
