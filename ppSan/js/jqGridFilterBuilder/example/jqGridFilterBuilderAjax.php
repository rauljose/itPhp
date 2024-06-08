<?php
require_once(__DIR__ . "/../../../../inc/iaJqGrid/Filter2where.php");

echo (new Filter2where(['fullTextCol']))->
        filter2where(json_encode($_REQUEST['filter'] ?? [], true));
