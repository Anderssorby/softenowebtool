<?php
//determine which site @author anders
$starttime = microtime(true);
session_start();
session_name("webtool");
$lifetime = 600;
setcookie(session_name(), session_id(), time()+$lifetime);
require('lib/load.php');
$mngr = new PageManager();
$page = $mngr->loadPage();
$page->create();
$stoptime = microtime(true);
$page->document->body->addChild(new HtmlComment("script took:" . ($stoptime - $starttime) . " secounds"));
echo $page->document;