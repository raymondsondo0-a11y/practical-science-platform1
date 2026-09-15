<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$config=require __DIR__.'/config.php';
if(empty($config['name'])||empty($config['user'])){http_response_code(503);echo json_encode(['ok'=>false,'error'=>'Database is not configured. Open setup.php first.']);exit;}
try{$pdo=new PDO("mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}",$config['user'],$config['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}catch(Throwable $e){http_response_code(503);echo json_encode(['ok'=>false,'error'=>'Database connection failed.']);exit;}
function out($x,$code=200){http_response_code($code);echo json_encode($x,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function poststr($k){return trim((string)($_POST[$k]??''));}
$action=$_GET['action']??'list';
try{
 if($action==='list'){
   $stmt=$pdo->query("SELECT t.*,COALESCE(SUM(p.people),0) joined FROM trips t LEFT JOIN participants p ON p.trip_id=t.id WHERE t.status='published' AND t.departure_at >= NOW() GROUP BY t.id ORDER BY t.departure_at ASC");
   out(['ok'=>true,'trips'=>$stmt->fetchAll()]);
 }
 if($action==='join' && $_SERVER['REQUEST_METHOD']==='POST'){
   $trip=(int)($_POST['trip_id']??0); $name=poststr('name'); $phone=poststr('phone'); $email=poststr('email'); $people=max(1,(int)($_POST['people']??1)); $message=poststr('message');
   if(!$trip||mb_strlen($name)<2||mb_strlen($phone)<6||!filter_var($email,FILTER_VALIDATE_EMAIL)) out(['ok'=>false,'error'=>'Please enter valid registration details.'],422);
   $pdo->beginTransaction();
   $s=$pdo->prepare("SELECT capacity,COALESCE((SELECT SUM(people) FROM participants WHERE trip_id=t.id),0) joined FROM trips t WHERE t.id=? AND t.status='published' AND t.departure_at>NOW() FOR UPDATE");$s->execute([$trip]);$t=$s->fetch();
   if(!$t){$pdo->rollBack();out(['ok'=>false,'error'=>'This trip is no longer available.'],404);}
   if($t['joined']+$people>$t['capacity']){$pdo->rollBack();out(['ok'=>false,'error'=>'Not enough spaces remain for this trip.'],409);}
   $q=$pdo->prepare('INSERT INTO participants(trip_id,full_name,phone,email,people,message) VALUES(?,?,?,?,?,?)');
   try{$q->execute([$trip,$name,$phone,$email,$people,$message]);}catch(PDOException $e){$pdo->rollBack();if((int)$e->errorInfo[1]===1062)out(['ok'=>false,'error'=>'This email is already registered for this trip.'],409);throw $e;}
   $pdo->commit();out(['ok'=>true,'message'=>'Registration confirmed.']);
 }
 if($action==='create' && $_SERVER['REQUEST_METHOD']==='POST'){
   $title=poststr('title');$destination=poststr('destination');$description=poststr('description');$departure=poststr('departure');$capacity=max(2,(int)($_POST['capacity']??12));$organizer=poststr('organizer');$phone=poststr('phone');
   if(!$title||!$destination||!$description||!$departure||!$organizer||!$phone)out(['ok'=>false,'error'=>'Please complete all required fields.'],422);
   $dt=DateTime::createFromFormat('Y-m-d\\TH:i',$departure) ?: DateTime::createFromFormat('Y-m-d H:i',$departure);
   if(!$dt || $dt->getTimestamp()<=time())out(['ok'=>false,'error'=>'Choose a future departure date and time.'],422);
   $images=['Ngorongoro'=>'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1200&q=80','Serengeti'=>'https://images.unsplash.com/photo-1519659528534-7fd733a832a0?auto=format&fit=crop&w=1200&q=80','Zanzibar'=>'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1200&q=80','Kilimanjaro'=>'https://images.unsplash.com/photo-1609198092458-38a293c7ac4b?auto=format&fit=crop&w=1200&q=80'];$image='https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1200&q=80';foreach($images as $key=>$url){if(stripos($destination,$key)!==false){$image=$url;break;}}
   $q=$pdo->prepare('INSERT INTO trips(title,destination,description,departure_at,capacity,organizer_name,organizer_phone,image_url) VALUES(?,?,?,?,?,?,?,?)');$q->execute([$title,$destination,$description,$dt->format('Y-m-d H:i:s'),$capacity,$organizer,$phone,$image]);out(['ok'=>true,'trip_id'=>$pdo->lastInsertId(),'message'=>'Trip published successfully.']);
 }
 out(['ok'=>false,'error'=>'Unknown action.'],400);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();out(['ok'=>false,'error'=>'Server error. Please try again.'],500);}
