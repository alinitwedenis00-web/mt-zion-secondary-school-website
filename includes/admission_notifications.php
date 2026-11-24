<?php
/** Notification adapter. Email uses PHP mail(); configure a working mail transport on hosting.
 * SMS uses Africa's Talking when AT_USERNAME and AT_API_KEY are configured in the server environment.
 */
function notify_admission_applicant(int $applicationId, string $status, string $message): array {
    global $pdo;
    $q=$pdo->prepare('SELECT id,application_no,full_name,email,phone,parent_name,parent_phone,parent_email FROM admission_enquiries WHERE id=?');
    $q->execute([$applicationId]); $a=$q->fetch();
    if (!$a) return ['email'=>false,'sms'=>false];
    $subject='Mt Zion admission update - '.$a['application_no'];
    $body="Dear ".$a['full_name'].",\n\nThere is an update to your Mt Zion Secondary School admission application.\n\nReference: ".$a['application_no']."\nStatus: ".$status."\nMessage: ".$message."\n\nKeep your reference safe and track progress on the school website using your reference and applicant email.\n\nMt Zion Secondary School Mubende";
    $localConfigFile=__DIR__.'/../config/notifications.local.php';
    $localConfig=is_file($localConfigFile) ? (require $localConfigFile) : [];
    if (!is_array($localConfig)) $localConfig=[];
    $from=getenv('ADMISSIONS_FROM_EMAIL') ?: ($localConfig['from_email'] ?? 'admissions@localhost');
    $emailTo=$a['email']; $emailOk=false; $smsOk=false; $emailError=''; $smsError='';
    if (filter_var($emailTo,FILTER_VALIDATE_EMAIL) && $from!=='admissions@localhost') {
        $headers='From: Mt Zion Admissions <'.$from.'>'."\r\n".'Content-Type: text/plain; charset=UTF-8';
        $emailOk=@mail($emailTo,$subject,$body,$headers);
        if (!$emailOk) $emailError='mail() did not accept the message; configure hosting SMTP/mail transport.';
    } else { $emailError='Admissions sender email is not configured. Set ADMISSIONS_FROM_EMAIL and configure mail transport.'; }
    $username=getenv('AT_USERNAME') ?: ($localConfig['at_username'] ?? '');
    $apiKey=getenv('AT_API_KEY') ?: ($localConfig['at_api_key'] ?? '');
    $smsTo=preg_replace('/[^0-9+]/','',(string)($a['parent_phone'] ?: $a['phone']));
    if (preg_match('/^0[0-9]{9}$/',$smsTo)) $smsTo='+256'.substr($smsTo,1);
    if (preg_match('/^256[0-9]{9}$/',$smsTo)) $smsTo='+'.$smsTo;
    if ($username!=='' && $apiKey!=='' && $smsTo!=='' && function_exists('curl_init')) {
        $smsText='Mt Zion: '.$a['application_no'].' status: '.$status.'. '.$message;
        $payload=http_build_query(['username'=>$username,'to'=>$smsTo,'message'=>$smsText]);
        $ch=curl_init('https://api.africastalking.com/version1/messaging');
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['apiKey: '.$apiKey,'Accept: application/json','Content-Type: application/x-www-form-urlencoded'],CURLOPT_TIMEOUT=>20]);
        $response=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); $curlErr=curl_error($ch); curl_close($ch);
        $smsOk=($response!==false && $code>=200 && $code<300);
        if (!$smsOk) $smsError=$curlErr ?: ('SMS gateway returned HTTP '.$code);
    } else { $smsError='SMS is not configured (AT_USERNAME / AT_API_KEY and a valid phone number are required).'; }
    $log=$pdo->prepare('INSERT INTO admission_notification_log(application_id,channel,recipient,notification_status,provider_response,created_at) VALUES(?,?,?,?,?,NOW())');
    foreach ([['email',$emailTo,$emailOk,$emailError],['sms',$smsTo,$smsOk,$smsError]] as $n) {
        $log->execute([$applicationId,$n[0],$n[1],$n[2]?'accepted':'not_sent',substr($n[3],0,1000)]);
    }
    return ['email'=>$emailOk,'sms'=>$smsOk,'email_error'=>$emailError,'sms_error'=>$smsError];
}
