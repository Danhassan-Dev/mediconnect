<?php
require_once __DIR__.'/includes/bootstrap.php';
if (is_logged_in()) { header('Location: login.php'); exit; }
$errors=[]; $v=['full_name'=>'','email'=>'','specialty'=>'','qualification'=>'','license_number'=>'','years_experience'=>'','consultation_fee'=>'','biography'=>''];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf();
 foreach ($v as $k=>$x) $v[$k]=trim((string)($_POST[$k]??''));
 $pw=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm_password']??'');
 if (mb_strlen($v['full_name'])<2 || mb_strlen($v['full_name'])>120) $errors[]='Enter a valid full name.';
 if (!filter_var($v['email'],FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email.';
 if ($v['specialty']==='' || $v['qualification']==='' || $v['license_number']==='') $errors[]='Specialty, qualification and license number are required.';
 if ($v['years_experience']!=='' && (!ctype_digit($v['years_experience']) || (int)$v['years_experience']>70)) $errors[]='Experience must be between 0 and 70.';
 if ($v['consultation_fee']!=='' && (!is_numeric($v['consultation_fee']) || (float)$v['consultation_fee']<0)) $errors[]='Enter a valid fee.';
 if (strlen($pw)<10) $errors[]='Password must contain at least 10 characters.';
 if ($pw!==$confirm) $errors[]='Passwords do not match.';
 if (!$errors) {
  $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$v['email']]);
  if ($s->fetch()) $errors[]='This email is already registered.';
  else try {
   $pdo->beginTransaction();
   $s=$pdo->prepare("INSERT INTO users(full_name,email,password_hash,role,status) VALUES(?,?,?,'doctor','active')");
   $s->execute([$v['full_name'],$v['email'],password_hash($pw,PASSWORD_DEFAULT)]);
   $id=(int)$pdo->lastInsertId();
   $s=$pdo->prepare('INSERT INTO doctor_profiles(user_id,specialty,qualification,license_number,years_experience,biography,consultation_fee,is_approved) VALUES(?,?,?,?,?,?,?,0)');
   $s->execute([$id,$v['specialty'],$v['qualification'],$v['license_number'],$v['years_experience']===''?null:(int)$v['years_experience'],$v['biography']?:null,$v['consultation_fee']===''?null:$v['consultation_fee']]);
   $pdo->commit(); flash('Application submitted. Your profile must be verified and approved before you can sign in.'); header('Location: login.php'); exit;
  } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); error_log($e->getMessage()); $errors[]='Could not submit application. Try again.'; }
 }
}
function val($k,$v){return e($v[$k]??'');}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Doctor application | MediConnect</title><link rel="stylesheet" href="assets/css/auth.css"></head><body><main class="auth-shell"><section class="auth-card wide"><a class="brand" href="index.php">✚ MediConnect</a><p class="eyebrow">DOCTOR PORTAL</p><h1>Apply as a doctor</h1><p class="muted">Your profile remains hidden until an administrator verifies and approves it.</p>
<?php if($errors):?><div class="alert error"><ul><?php foreach($errors as $x):?><li><?=e($x)?></li><?php endforeach;?></ul></div><?php endif;?>
<form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
<?php foreach(['full_name'=>'Full name','email'=>'Email address','specialty'=>'Medical specialty','qualification'=>'Qualification','license_number'=>'Professional license number','years_experience'=>'Years of experience','consultation_fee'=>'Consultation fee (₦)'] as $k=>$label):?><label><?=e($label)?><input name="<?=$k?>" value="<?=val($k,$v)?>" <?=$k==='email'?'type="email"':($k==='consultation_fee'?'type="number" min="0" step="0.01"':($k==='years_experience'?'type="number" min="0" max="70"':''))?> <?=in_array($k,['full_name','email','specialty','qualification','license_number'])?'required':''?>></label><?php endforeach;?>
<label>Professional biography<textarea name="biography" rows="4"><?=val('biography',$v)?></textarea></label>
<label>Password<input type="password" name="password" minlength="10" required></label><label>Confirm password<input type="password" name="confirm_password" minlength="10" required></label>
<button class="primary-button">Submit application</button></form><p class="switch-link"><a href="login.php">Return to sign in</a></p></section></main></body></html>
