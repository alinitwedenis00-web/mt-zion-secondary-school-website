<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Admissions | Mt Zion Secondary School Mubende';
$success = '';
$error = '';
$reference = '';
$allowedClasses = ['S.1','S.2','S.3','S.4','S.5','S.6'];

function admission_upload(string $field, array $allowedMime, int $maxBytes = 3145728): ?array {
    if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('A document could not be uploaded. Check the file and try again.');
    }
    if ((int)$file['size'] <= 0) throw new RuntimeException('The uploaded document is empty. Please choose another file.');
    if ((int)$file['size'] > $maxBytes) throw new RuntimeException('Each uploaded document must be 3 MB or smaller.');

    // Do not rely on finfo alone: Windows/PHP installations can report valid
    // JPEG/PDF uploads as application/octet-stream or another MIME alias.
    // Verify the file signature/content and its extension, then store a
    // canonical MIME type. This still rejects renamed or disguised files.
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $mime = '';
    $storedExtension = '';

    if (in_array($extension, ['jpg', 'jpeg'], true)) {
        $image = @getimagesize($file['tmp_name']);
        if ($image !== false && ($image[2] ?? null) === IMAGETYPE_JPEG) {
            $mime = 'image/jpeg';
            $storedExtension = 'jpg';
        }
    } elseif ($extension === 'png') {
        $image = @getimagesize($file['tmp_name']);
        if ($image !== false && ($image[2] ?? null) === IMAGETYPE_PNG) {
            $mime = 'image/png';
            $storedExtension = 'png';
        }
    } elseif ($extension === 'pdf') {
        $handle = @fopen($file['tmp_name'], 'rb');
        $signature = $handle ? fread($handle, 5) : false;
        if ($handle) fclose($handle);
        if ($signature === '%PDF-') {
            $mime = 'application/pdf';
            $storedExtension = 'pdf';
        }
    }

    if ($mime === '' || !isset($allowedMime[$mime])) {
        throw new RuntimeException('Unsupported document type. For the passport photograph use a genuine JPG or PNG image; for the academic report use a genuine PDF, JPG or PNG file.');
    }

    $dir = __DIR__ . '/storage/admissions';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('Document storage is unavailable.');
    $filename = bin2hex(random_bytes(20)) . '.' . $storedExtension;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) throw new RuntimeException('Could not save an uploaded document.');

    return [
        'stored_name' => $filename,
        'original_name' => substr(basename($file['name']), 0, 190),
        'mime_type' => $mime,
        'file_size' => (int)$file['size']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (!empty($_POST['website'])) {
        $success = 'Your application has been received.';
    } else {
        $data = [
            'full_name'=>trim($_POST['full_name'] ?? ''),
            'email'=>trim($_POST['email'] ?? ''),
            'phone'=>trim($_POST['phone'] ?? ''),
            'class_applying'=>trim($_POST['class_applying'] ?? ''),
            'date_of_birth'=>trim($_POST['date_of_birth'] ?? ''),
            'gender'=>trim($_POST['gender'] ?? ''),
            'parent_name'=>trim($_POST['parent_name'] ?? ''),
            'parent_phone'=>trim($_POST['parent_phone'] ?? ''),
            'parent_email'=>trim($_POST['parent_email'] ?? ''),
            'address'=>trim($_POST['address'] ?? ''),
            'previous_school'=>trim($_POST['previous_school'] ?? ''),
            'last_class'=>trim($_POST['last_class'] ?? ''),
            'year_completed'=>trim($_POST['year_completed'] ?? ''),
            'results_summary'=>trim($_POST['results_summary'] ?? ''),
            'message'=>trim($_POST['message'] ?? ''),
        ];
        try {
            if ($data['full_name']==='' || mb_strlen($data['full_name'])>150) throw new RuntimeException('Enter the applicant’s full name (maximum 150 characters).');
            if (!filter_var($data['email'],FILTER_VALIDATE_EMAIL) || strlen($data['email'])>190) throw new RuntimeException('Enter a valid applicant email address.');
            if (!in_array($data['class_applying'],$allowedClasses,true)) throw new RuntimeException('Select a valid class.');
            if ($data['parent_name']==='' || $data['parent_phone']==='') throw new RuntimeException('Parent/guardian name and telephone number are required.');
            if ($data['parent_email']!=='' && !filter_var($data['parent_email'],FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid parent/guardian email address.');
            if (!in_array($data['gender'],['Male','Female','Prefer not to say'],true)) throw new RuntimeException('Select the applicant’s gender.');
            if (empty($_POST['declaration'])) throw new RuntimeException('Please confirm the application declaration.');
            if ($data['date_of_birth']!=='' && (strtotime($data['date_of_birth'])===false || $data['date_of_birth']>date('Y-m-d'))) throw new RuntimeException('Enter a valid date of birth.');

            $photo = admission_upload('passport_photo',['image/jpeg'=>'jpg','image/png'=>'png']);
            $report = admission_upload('academic_report',['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png']);
            $reference = 'MZS-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $pdo->beginTransaction();
            $stmt=$pdo->prepare("INSERT INTO admission_enquiries
                (application_no,full_name,email,phone,class_applying,message,status,date_of_birth,gender,parent_name,parent_phone,parent_email,applicant_address,previous_school,last_class,year_completed,results_summary,created_at,updated_at)
                VALUES (?,?,?,?,?,?, 'Submitted',?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");
            $stmt->execute([$reference,$data['full_name'],$data['email'],$data['phone'],$data['class_applying'],$data['message'],$data['date_of_birth']?:null,$data['gender'],$data['parent_name'],$data['parent_phone'],$data['parent_email'],$data['address'],$data['previous_school'],$data['last_class'],$data['year_completed']?:null,$data['results_summary']]);
            $applicationId=(int)$pdo->lastInsertId();
            $history=$pdo->prepare("INSERT INTO admission_status_history(application_id,old_status,new_status,changed_by,comment) VALUES(?,NULL,'Submitted','Applicant','Application submitted online')");
            $history->execute([$applicationId]);
            foreach (['passport_photo'=>$photo,'academic_report'=>$report] as $kind=>$file) {
                if ($file) {
                    $doc=$pdo->prepare("INSERT INTO admission_documents(application_id,document_type,stored_name,original_name,mime_type,file_size) VALUES(?,?,?,?,?,?)");
                    $doc->execute([$applicationId,$kind,$file['stored_name'],$file['original_name'],$file['mime_type'],$file['file_size']]);
                }
            }
            $pdo->commit();
            if (function_exists('notify_admission_applicant')) {
                notify_admission_applicant($applicationId,'Submitted','Your application has been received. The school will review it and update you.');
            }
            $success='Your application has been submitted successfully. Keep your application reference safe.';
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($ex instanceof RuntimeException) $error=$ex->getMessage();
            else { error_log('Admission submission failed: '.$ex->getMessage()); $error='We could not submit your application just now. Please try again or contact the school.'; }
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
<h1>
<?=e(setting('admissions_heading', 'Online Admissions Portal'))?>
</h1>
<p>
<?=e(setting('admissions_intro', setting('admissions_text', 'Complete the application, submit supporting documents and use your reference number to follow its progress.')))?>
</p>
<a class="btn" href="#application">Start Application</a> <a class="btn btn-dark" href="admission-track.php">Track an Application</a>
</section>
<section class="section admissions-layout">
<div>
<h2>Join Mt Zion Secondary School</h2>
<ul class="check-list">
<li>Applications for Senior One to Senior Six</li>
<li>Applicant and parent/guardian information</li>
<li>Supporting academic documents</li>
<li>Application reference for progress tracking</li>
</ul>
<img class="wide-photo" src="assets/images/school/O BRIEFING.jpg" alt="Students at Mt Zion Secondary School">
<div class="notice"><strong>Before you begin:</strong> Have the applicant’s details and any available academic report ready. A parent or guardian’s contact is required.</div>
</div>
<div id="application">
<h2>Student Application</h2>
<?php if ($success): ?>
<div class="success">
<?=e($success)?>
<?php if ($reference): ?>
<br>
<strong>Application reference: <?=e($reference)?>
</strong>
<br>
<a href="admission-track.php">Track your application</a>
<?php endif; ?>
</div>
<?php endif; ?>
<?php if ($error): ?><div class="error"><?=e($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label class="hp-field" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
<h3>1. Applicant information</h3>
<label>Applicant full name *<input name="full_name" maxlength="150" required></label>
<div class="form-row">
<label>Date of birth<input type="date" name="date_of_birth" max="<?=e(date('Y-m-d'))?>">
</label>
<label>Gender *<select name="gender" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Prefer not to say</option>
</select>
</label>
</div>
<label>Applicant email address *<input type="email" name="email" maxlength="190" required></label>
<label>Applicant telephone number<input name="phone" maxlength="50" placeholder="e.g. +256..."></label>
<label>Class applying for *<select name="class_applying" required>
<option value="">Choose class</option>
<?php foreach ($allowedClasses as $c): ?>
<option value="<?=e($c)?>">
<?=e($c)?>
</option>
<?php endforeach; ?>
</select>
</label>
<label>Home address / village / district<textarea name="address" rows="2" maxlength="1000"></textarea></label>
<h3>2. Parent / guardian information</h3>
<label>Parent/guardian full name *<input name="parent_name" maxlength="150" required></label>
<label>Parent/guardian telephone *<input name="parent_phone" maxlength="50" required></label>
<label>Parent/guardian email<input type="email" name="parent_email" maxlength="190"></label>
<h3>3. Previous education</h3>
<label>Previous school<input name="previous_school" maxlength="190"></label>
<div class="form-row">
<label>Last class attended<input name="last_class" maxlength="50" placeholder="e.g. Primary Seven">
</label>
<label>Year completed<input type="number" name="year_completed" min="1990" max="<?=e(date('Y')+1)?>">
</label>
</div>
<label>Academic results / subjects / additional information<textarea name="results_summary" rows="3" maxlength="5000"></textarea></label>
<h3>4. Supporting documents</h3>
<label>Passport photograph (JPG/PNG, maximum 3 MB)<input type="file" name="passport_photo" accept="image/jpeg,image/png"></label>
<label>Academic report (PDF/JPG/PNG, maximum 3 MB)<input type="file" name="academic_report" accept="application/pdf,image/jpeg,image/png"></label>
<h3>5. Additional message</h3><label>Message to the admissions office<textarea name="message" rows="3" maxlength="5000"></textarea></label>
<label class="checkbox-line">
<input type="checkbox" name="declaration" value="1" required> I confirm that the information provided is accurate and consent to the school using it to process this admission application. *</label>
<p class="small-note">Your information is for admissions processing. Do not upload original certificates; provide copies only.</p>
<button class="btn" type="submit">Submit Admission Application</button>
</form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
