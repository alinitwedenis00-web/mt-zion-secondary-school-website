<?php
require_once '_init.php';
$title='Admission Applications'; $message=''; $notificationInfo='';
$allowedStatuses=['Submitted','Under Review','Shortlisted','Admitted','Rejected'];

if (isset($_GET['export']) && $_GET['export']==='csv') {
    $export=$pdo->query("SELECT application_no,full_name,email,phone,class_applying,status,parent_name,parent_phone,previous_school,created_at,updated_at FROM admission_enquiries ORDER BY created_at DESC,id DESC");
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="mt-zion-admissions-'.date('Ymd-His').'.csv"');
    $out=fopen('php://output','w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out,['Application reference','Applicant','Email','Phone','Class','Status','Parent/guardian','Parent phone','Previous school','Submitted','Last updated']);
    while ($row=$export->fetch(PDO::FETCH_ASSOC)) {
        // Prefix formula-like user input to prevent spreadsheet formula injection.
        foreach ($row as $key=>$value) {
            if (is_string($value) && preg_match('/^[=+@\\-\\t\\r]/',$value)) $row[$key]="'".$value;
        }
        fputcsv($out,array_values($row));
    }
    fclose($out);
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check(); $action=$_POST['action']??''; $id=(int)($_POST['id']??0);
    if ($action==='update' && $id>0) {
        $status=trim($_POST['status']??'Submitted'); $comment=trim($_POST['admin_comment']??'');
        if (!in_array($status,$allowedStatuses,true)) $message='Select a valid application status.';
        else {
            $q=$pdo->prepare('SELECT status FROM admission_enquiries WHERE id=?'); $q->execute([$id]); $old=$q->fetchColumn();
            if ($old===false) $message='Application not found.';
            else {
                $pdo->beginTransaction();
                $u=$pdo->prepare('UPDATE admission_enquiries SET status=?,admin_comment=?,updated_at=NOW() WHERE id=?'); $u->execute([$status,$comment,$id]);
                if ($old!==$status || $comment!=='') { $h=$pdo->prepare('INSERT INTO admission_status_history(application_id,old_status,new_status,changed_by,comment) VALUES(?,?,?,?,?)'); $h->execute([$id,$old,$status,'Administrator',$comment!==''?$comment:'Status changed by school']); }
                $pdo->commit();
                $message='Application updated.';
                if ($old!==$status || $comment!=='') {
                    $sent=notify_admission_applicant($id,$status,$comment!==''?$comment:'Your application status has been updated.');
                    $notificationInfo='Notification result — Email: '.($sent['email']?'accepted by mail server':'not sent/configuration needed').'; SMS: '.($sent['sms']?'accepted by SMS gateway':'not sent/configuration needed').'.';
                }
            }
        }
    } elseif ($action==='delete' && $id>0) {
        // Remove stored files as well as cascading database records.
        $q=$pdo->prepare('SELECT stored_name FROM admission_documents WHERE application_id=?');
        $q->execute([$id]);
        $storage=realpath(__DIR__.'/../storage/admissions');
        foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $storedName) {
            if ($storage && basename($storedName)===$storedName) {
                $candidate=$storage.DIRECTORY_SEPARATOR.$storedName;
                if (is_file($candidate)) @unlink($candidate);
            }
        }
        $pdo->prepare('DELETE FROM admission_enquiries WHERE id=?')->execute([$id]);
        $message='Application and related records deleted.';
    }
}
$edit=null; $docs=[]; $history=[]; $logs=[];
if (isset($_GET['edit'])) {
    $q=$pdo->prepare('SELECT * FROM admission_enquiries WHERE id=?'); $q->execute([(int)$_GET['edit']]); $edit=$q->fetch();
    if ($edit) {
        $q=$pdo->prepare('SELECT * FROM admission_documents WHERE application_id=? ORDER BY id'); $q->execute([$edit['id']]); $docs=$q->fetchAll();
        $q=$pdo->prepare('SELECT * FROM admission_status_history WHERE application_id=? ORDER BY created_at DESC'); $q->execute([$edit['id']]); $history=$q->fetchAll();
        $q=$pdo->prepare('SELECT * FROM admission_notification_log WHERE application_id=? ORDER BY created_at DESC LIMIT 10'); $q->execute([$edit['id']]); $logs=$q->fetchAll();
    }
}
$search=trim($_GET['q']??''); $statusFilter=trim($_GET['status']??'');
$sql="SELECT a.*,COALESCE(c.display_name,a.class_applying) AS class_display,COALESCE(c.level_group,'Unclassified') AS level_group FROM admission_enquiries a LEFT JOIN admission_classes c ON c.class_code=a.class_applying WHERE 1=1"; $params=[];
if ($search!=='') { $sql.=' AND (a.full_name LIKE ? OR a.application_no LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)'; $like='%'.$search.'%'; array_push($params,$like,$like,$like,$like); }
if ($statusFilter!=='' && in_array($statusFilter,$allowedStatuses,true)) { $sql.=' AND a.status=?'; $params[]=$statusFilter; }
$sql.=' ORDER BY a.created_at DESC,a.id DESC'; $q=$pdo->prepare($sql); $q->execute($params); $rows=$q->fetchAll();
$classSummary=$pdo->query("SELECT c.display_name,c.level_group,COUNT(a.id) application_count FROM admission_classes c LEFT JOIN admission_enquiries a ON a.class_applying=c.class_code GROUP BY c.class_code,c.display_name,c.level_group ORDER BY c.class_code")->fetchAll();
$counts=$pdo->query("SELECT COUNT(*) total,SUM(status IN ('Submitted','Under Review')) pending,SUM(status='Admitted') admitted,SUM(status='Rejected') rejected FROM admission_enquiries")->fetch();
require '_layout.php';
?>
<h1>Admissions Management Portal</h1>
<p>Review complete applications, inspect supporting documents, update decisions and notify applicants.</p>
<?php if($message): ?>
<div class="success">
<?=e($message)?>
</div>
<?php endif; ?>
<?php if($notificationInfo): ?>
<div class="notice">
<?=e($notificationInfo)?>
</div>
<?php endif; ?>
<div class="admin-cards">
<div>
<strong>
<?=(int)$counts['total']?>
</strong>
<span>Total applications</span>
</div>
<div>
<strong>
<?=(int)$counts['pending']?>
</strong>
<span>Awaiting decision</span>
</div>
<div>
<strong>
<?=(int)$counts['admitted']?>
</strong>
<span>Admitted</span>
</div>
<div>
<strong>
<?=(int)$counts['rejected']?>
</strong>
<span>Rejected</span>
</div>
</div>
<?php if($edit): ?>
<section class="admin-form">
<h2>Application details: <?=e($edit['application_no']??('Record #'.$edit['id']))?>
</h2>
<div class="detail-grid">
<p>
<b>Applicant:</b> <?=e($edit['full_name'])?>
</p>
<p>
<b>Email:</b> <?=e($edit['email'])?>
</p>
<p>
<b>Phone:</b> <?=e($edit['phone'])?>
</p>
<p>
<b>Date of birth:</b> <?=e($edit['date_of_birth']??'Not supplied')?>
</p>
<p>
<b>Gender:</b> <?=e($edit['gender']??'')?>
</p>
<p>
<b>Class:</b> <?=e($edit['class_applying'])?>
</p>
<p>
<b>Parent/guardian:</b> <?=e($edit['parent_name']??'')?>
</p>
<p>
<b>Parent phone:</b> <?=e($edit['parent_phone']??'')?>
</p>
<p>
<b>Parent email:</b> <?=e($edit['parent_email']??'')?>
</p>
<p>
<b>Address:</b> <?=e($edit['applicant_address']??'')?>
</p>
<p>
<b>Previous school:</b> <?=e($edit['previous_school']??'')?>
</p>
<p>
<b>Last class:</b> <?=e($edit['last_class']??'')?>
</p>
<p>
<b>Year completed:</b> <?=e($edit['year_completed']??'')?>
</p>
<p>
<b>Results:</b> <?=e($edit['results_summary']??'')?>
</p>
<p>
<b>Additional message:</b> <?=e($edit['message']??'')?>
</p>
</div>
<h3>Supporting documents</h3>
<?php if($docs): ?>
<ul>
<?php foreach($docs as $doc): ?>
<li>
<?=e($doc['document_type'])?> — <?=e($doc['original_name'])?> (<?=number_format($doc['file_size']/1024,1)?> KB) <a href="admission-document.php?id=<?=(int)$doc['id']?>">Download</a>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<p>No documents attached.</p>
<?php endif; ?>
<form method="post" class="form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" value="<?=(int)$edit['id']?>">
<label>Application status<select name="status">
<?php foreach($allowedStatuses as $s): ?>
<option value="<?=e($s)?>" <?=($edit['status']??'Submitted')===$s?'selected':''?>>
<?=e($s)?>
</option>
<?php endforeach; ?>
</select>
</label>
<label>Message to applicant (included in email/SMS where configured)<textarea name="admin_comment" rows="3">
<?=e($edit['admin_comment']??'')?>
</textarea>
</label>
<button class="btn btn-dark" type="submit">Save status and notify applicant</button>
<a class="btn" href="admissions.php">Close details</a>
</form>
<h3>Progress history</h3>
<ul class="timeline">
<?php foreach($history as $h): ?>
<li>
<b>
<?=e($h['old_status']?:'New')?> → <?=e($h['new_status'])?>
</b>
<br>
<?=e($h['comment'])?>
<br>
<small>
<?=e($h['created_at'])?>
</small>
</li>
<?php endforeach; ?>
</ul>
<h3>Notification log</h3>
<div class="table-wrap">
<table>
<tr>
<th>Channel</th>
<th>Recipient</th>
<th>Result</th>
<th>Date</th>
</tr>
<?php foreach($logs as $l): ?>
<tr>
<td>
<?=e($l['channel'])?>
</td>
<td>
<?=e($l['recipient'])?>
</td>
<td>
<?=e($l['notification_status'])?> — <?=e($l['provider_response'])?>
</td>
<td>
<?=e($l['created_at'])?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</section>
<?php endif; ?>
<h2>Applications by class</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Class</th>
<th>Level</th>
<th>Applications</th>
</tr>
</thead>
<tbody>
<?php foreach($classSummary as $s): ?>
<tr>
<td>
<?=e($s['display_name'])?>
</td>
<td>
<?=e($s['level_group'])?>
</td>
<td>
<?=(int)$s['application_count']?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<h2>All applications (<?=count($rows)?>)</h2>
<p>
<a class="btn" href="admissions.php?export=csv">Export applications to CSV</a>
</p>
<form method="get" class="filter-form">
<input name="q" value="<?=e($search)?>" placeholder="Search name, reference, email or phone">
<select name="status">
<option value="">All statuses</option>
<?php foreach($allowedStatuses as $s): ?>
<option <?=($statusFilter===$s?'selected':'')?> value="<?=e($s)?>">
<?=e($s)?>
</option>
<?php endforeach; ?>
</select>
<button class="btn" type="submit">Filter</button>
</form>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Submitted</th>
<th>Reference / applicant</th>
<th>Contact</th>
<th>Class</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td>
<?=e($r['created_at'])?>
</td>
<td>
<b>
<?=e($r['application_no']??'Legacy record')?>
</b>
<br>
<?=e($r['full_name'])?>
</td>
<td>
<?=e($r['email'])?>
<br>
<?=e($r['phone'])?>
</td>
<td>
<?=e($r['class_display'])?>
</td>
<td>
<?=e($r['status']??'Pending')?>
</td>
<td>
<a class="text-link" href="admissions.php?edit=<?=(int)$r['id']?>">Review</a>
<form method="post" class="inline-form" onsubmit="return confirm('Permanently delete this application and its history?')">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?=(int)$r['id']?>">
<button class="danger-link" type="submit">Delete</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if(!$rows): ?>
<tr>
<td colspan="6">No applications found.</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
<?php require '_end.php'; ?>
