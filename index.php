<?php

define( 'WORKSPACE_DIR', 'workspace/' );
define( 'COMMONS_DIR', 'common/' );
define( 'DIST_DIR', 'dist/' );
define( 'TEMPLATES_DIR', 'templates/' );
define( 'PARTIALS_DIR', 'partials/' );

// NEED SOME GUI

require_once "classes/class-parser.php";
require_once "classes/class-sender.php";

$parser = new Parser( 'test-workspace' );
$parser->parse();

$sender = new Sender( 'test-workspace' );
$sender->sendAll();