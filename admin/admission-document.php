<?php
require_once '_init.php';
$id=(int)($_GET['id']??0);
$q=$pdo->prepare('SELECT stored_name,original_name,mime_type FROM admission_documents WHERE id=?');
$q->execute([$id]); $doc=$q->fetch();
if (!$doc) { http_response_code(404); exit('Document not found.'); }
$base=realpath(__DIR__.'/../storage/admissions');
$file=$base ? realpath($base.DIRECTORY_SEPARATOR.$doc['stored_name']) : false;
if (!$file || strpos($file,$base.DIRECTORY_SEPARATOR)!==0 || !is_file($file)) { http_response_code(404); exit('Document unavailable.'); }
header('X-Content-Type-Options: nosniff');
header('Content-Type: '.$doc['mime_type']);
header('Content-Disposition: attachment; filename="'.str_replace(['"',"\r","\n"],'_',basename($doc['original_name'])).'"');
header('Content-Length: '.filesize($file));
readfile($file); exit;
