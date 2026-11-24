<?php
require_once '_init.php';
$title = 'Page Editor';
$pages = ['home'=>'Homepage custom content','about'=>'About page custom content','academics'=>'Academics page custom content','admissions'=>'Admissions page custom content','gallery'=>'Gallery page custom content','portfolio'=>'Portfolio page custom content','news'=>'News page custom content','contact'=>'Contact page custom content','contact-track'=>'Contact message tracking page custom content','admission-track'=>'Admission tracking page custom content','staff'=>'Staff page custom content','board'=>'Board page custom content'];
$notice='';
if ($_SERVER['REQUEST_METHOD']==='POST') { csrf_check(); $save=$pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)'); foreach($pages as $key=>$label){$save->execute(['page_html_'.$key, trim($_POST[$key]??'')]);} $notice='Page content saved.'; }
require '_layout.php';
?>
<h1>Page Editor</h1><p>Optional advanced page-content overrides. Enter HTML fragments only when you want to replace a page's normal content. Leave a field empty to keep the normal page layout and its database-powered modules (slider, news, gallery, admissions, etc.). Do not paste script tags.</p><?php if($notice):?><div class="success"><?=e($notice)?></div><?php endif; ?><form method="post" class="form admin-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><?php foreach($pages as $key=>$label):?><label><?=e($label)?><textarea name="<?=e($key)?>" rows="8"><?=e(setting('page_html_'.$key))?></textarea></label><?php endforeach; ?><button class="btn btn-dark">Save page overrides</button></form>
<?php require '_end.php'; ?>