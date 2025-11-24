<?php
session_start();
session_unset();
session_destroy();
header("Location: /v2");
exit;
