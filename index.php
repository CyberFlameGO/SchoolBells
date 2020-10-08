<?php

session_start();

#set username and password here
$users = array(
    'user'=>'pass'  
);

if (isset($_POST['user'])){
	if($_POST['password']==$users[$_POST['user']]){
		$_SESSION['user'] = $_POST['user'];
		$user = $_POST['user'];
	} else {
		$error = "Incorrect username or password";
		login($error);
		die();
	}
} else if(isset($_SESSION['user'])){
	$user = $_SESSION['user'];
	if(!in_array($user,array_keys($users))){
		login("user $user is not a user");
		die();
	}
} else {
	login($error);
	die();
}

$db = new PDO("sqlite:./bells.db");
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS weeklyschedule (
    description TEXT,
	time TEXT,
	mon INTEGER,
	tue INTEGER,
	wed INTEGER,
	thu INTEGER,
	fri INTEGER,
	sat INTEGER,
	sun INTEGER
);");
$db->exec("CREATE TABLE IF NOT EXISTS bells (
    description TEXT,
	date TEXT,
	time TEXT,
	PRIMARY KEY('date','time')
);");

function login($error){
	echo "
	<html>
	<head>
		<title>School Bells - Login</title>
		<style>
		body{
			font-family:sans-serif;
			text-align:center;
		}
		</style>
	</head>
	<body>
	<form method=post>
		<br>
		$error<br>
		<table style='margin:0 auto;'>
			<tr><td>Username<td><input name=user>
			<tr><td>Password<td><input name=password type=password>
		</table>
		<input type=submit value=Login>
	</form>
	</body>
	</html>";
}

function loadcontent($content,$function){
	echo "
	<html>
	<head>
		<title>School Bells</title>
	    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
		<style>
		body{
			font-family:sans-serif;
			text-align:left;
			font-size:14px;
		}
		#left {
		    position:fixed;
		    top:0px;
		    left:0px;
		    bottom:0px;
		    width:160px;
		    overflow-y:auto;
		    padding:20px;
		    background-color:#eee;
		}
		table {
		    border-collapse:collapse;
		}
		td, th {
		    border:1px solid #ccc;
		}
		h2 {
		    padding:0px;
		    margin:0px;
		}
		#left a {
		    text-decoration:none;
		    color:#000;
		    display:block;
		    margin-bottom:5px;
		}
		#content {
		    position:fixed;
		    top:0px;
		    left:0px;
		    bottom:0px;
		    left:200px;
		    overflow-y:auto;
		    padding:20px;
		}
		</style>
	</head>
	<body>
	<div id=left>
	    <h2>School Bells</h2>
	    <br>
	    <a href='./?f=main'>Home</a>
	    <a href='./?f=week'>Weekly Schedule</a>
	    <a href='./?f=ring'>Ring Now</a>
	    <a href='./?f=logout'>Logout</a>
	</div>
	<div id=content>
	$content
	</div>
	<script>
	    $('#left a[href$=$function]').css('font-weight','bold');
	</script>
	</body>
	</html>";
}

if(array_key_exists('f',$_GET)){
    $function = $_GET['f'];
} else {
    $function = 'main';
}

if($function=='main'){
	if(array_key_exists('date',$_GET)){
	    $date = $_GET['date'];
	} else {
	    $date = date('Y-m-d');
	}
	$dateq = $db->quote($date);
	$datef = date('j M Y',strtotime($date));
	$content = "
	<h2>Bells - $datef</h2>
	<form method=get action='./' id=changedateform>
	<input type=hidden name=f value=main>
	<input type=date id=date name=date onchange='changedate()' value=$dateq></form>
	<br>
	<table id=todayschedule>
    <thead>
        <tr>
            <th>Description
            <th>Time
            <th>Delete
    </thead>
    <tbody>";
    
    $data = $db->query("SELECT * FROM bells WHERE date=$dateq ORDER BY time ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach($data as $d){
    	$description = json_encode($d['description']);
    	$time = json_encode($d['time']);
		$content .= "
        <tr>
            <td><input name=description value=$description>
            <td><input type=time name=time value=$time>
            <td><button onclick='removerow(this)'>Delete</button>";
    }
            
    $content .= "
    </tbody>
    </table>
    <button onclick='addrow()'>Add Row</button> 
    <button onclick='save()'>Save</button> 
    <button onclick='clearall()'>Clear All</button> <br>
    <form method=post action='./?f=saveday' id=saveday><input type=hidden name=newdata id=savedaydata><input type=hidden name=date id=savedaydate></form>
    <script>
    	function removerow(item){
    		$(item).closest('tr').remove();
    	}
        function addrow(){
            $('#todayschedule tbody').append('<tr><td><input name=description><td><input type=time name=time><td><button onclick=\"removerow(this)\">Delete</button>');
        }
        function clearall(){
        	$('#todayschedule tbody tr').remove();
        	save();
        }
        function save(){
        	tosave = [];
            $('#todayschedule tbody tr').each(function(){
            	description = $(this).find('[name=description]').val();
            	time = $(this).find('[name=time]').val();
            	tosave.push({description,time});
            });
            console.log(tosave);
            $('#savedaydata').val(JSON.stringify(tosave));
            $('#savedaydate').val($('#date').val());
            $('#saveday').submit();
        }
        function changedate(){
        	$('#changedateform').submit();
        }
    </script>
    ";
	
	
    loadcontent($content,$function);
} else if($function=='week'){
    $content = "<h2>Weekly Schedule</h2><br>
    <table id=weeklyschedule>
    <thead>
        <tr>
            <th>Description
            <th>Time
            <th>Mon
            <th>Tue
            <th>Wed
            <th>Thu
            <th>Fri
            <th>Sat
            <th>Sun
            <th>Delete
    </thead>
    <tbody>";
    
    $data = $db->query("SELECT * FROM weeklyschedule ORDER BY time ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach($data as $d){
    	$description = json_encode($d['description']);
    	$time = json_encode($d['time']);
    	if($d['mon']==1){$mon="checked";}else{$mon="";}
    	if($d['tue']==1){$tue="checked";}else{$tue="";}
    	if($d['wed']==1){$wed="checked";}else{$wed="";}
    	if($d['thu']==1){$thu="checked";}else{$thu="";}
    	if($d['fri']==1){$fri="checked";}else{$fri="";}
    	if($d['sat']==1){$sat="checked";}else{$sat="";}
    	if($d['sun']==1){$sun="checked";}else{$sun="";}
		$content .= "
        <tr>
            <td><input name=description value=$description>
            <td><input type=time name=time value=$time>
            <td><input type=checkbox name=mon $mon>
            <td><input type=checkbox name=tue $tue>
            <td><input type=checkbox name=wed $wed>
            <td><input type=checkbox name=thu $thu>
            <td><input type=checkbox name=fri $fri>
            <td><input type=checkbox name=sat $sat>
            <td><input type=checkbox name=sun $sun>
            <td><button onclick='removerow(this)'>Delete</button>";
    }
            
    $content .= "
    </tbody>
    </table>
    <button onclick='addrow()'>Add Row</button> 
    <button onclick='save()'>Save</button> <br>
    <br>
    <b>Apply Schedule to dates (please save first):</b><br>
    <form method=post action='./?f=applytodates' id=applytodates>
    <input type=date name=firstdate> to <input type=date name=lastdate><br>
    <button onclick='applytodates()'>Apply to Dates</button> 
    </form>
    <form method=post action='./?f=saveweekly' id=saveweekly><input type=hidden name=newdata id=saveweeklynewdata></form>
    <input type=hidden name=start id=applytodatesstart><input type=hidden name=end id=applytodatesend></form>
    <script>
    	function removerow(item){
    		$(item).closest('tr').remove();
    	}
        function addrow(){
            $('#weeklyschedule tbody').append('<tr><td><input name=description><td><input type=time name=time><td><input type=checkbox name=mon><td><input type=checkbox name=tue><td><input type=checkbox name=wed><td><input type=checkbox name=thu><td><input type=checkbox name=fri><td><input type=checkbox name=sat><td><input type=checkbox name=sun><td><button onclick=\"removerow(this)\">Delete</button>');
        }
        function save(){
        	tosave = [];
            $('#weeklyschedule tbody tr').each(function(){
            	description = $(this).find('[name=description]').val();
            	time = $(this).find('[name=time]').val();
            	if ($(this).find('[name=mon]').is(':checked')) {mon=1;} else {mon=0;}
            	if ($(this).find('[name=tue]').is(':checked')) {tue=1;} else {tue=0;}
            	if ($(this).find('[name=wed]').is(':checked')) {wed=1;} else {wed=0;}
            	if ($(this).find('[name=thu]').is(':checked')) {thu=1;} else {thu=0;}
            	if ($(this).find('[name=fri]').is(':checked')) {fri=1;} else {fri=0;}
            	if ($(this).find('[name=sat]').is(':checked')) {sat=1;} else {sat=0;}
            	if ($(this).find('[name=sun]').is(':checked')) {sun=1;} else {sun=0;}
            	tosave.push({description,time,mon,tue,wed,thu,fri,sat,sun});
            });
            $('#saveweeklynewdata').val(JSON.stringify(tosave));
            $('#saveweekly').submit();
        }
        function applytodates(){
            $('#applytodates').submit();
        }
    </script>
    ";
    loadcontent($content,$function);
} else if($function=='saveweekly'){
    $db->exec("DELETE FROM weeklyschedule WHERE 1=1");
    $newdata = json_decode($_POST['newdata'],true);
    foreach($newdata as $d){
    	$description = $db->quote($d['description']);
    	$time = $db->quote($d['time']);
    	$mon = $db->quote($d['mon']);
    	$tue = $db->quote($d['tue']);
    	$wed = $db->quote($d['wed']);
    	$thu = $db->quote($d['thu']);
    	$fri = $db->quote($d['fri']);
    	$sat = $db->quote($d['sat']);
    	$sun = $db->quote($d['sun']);
    	$db->exec("INSERT INTO weeklyschedule VALUES ($description,$time,$mon,$tue,$wed,$thu,$fri,$sat,$sun)");
    }
    echo "Saved";
    header('location: ./?f=week');
} else if($function=='saveday'){
	$date = $_POST['date'];
	$dateq = $db->quote($_POST['date']);
    $db->exec("DELETE FROM bells WHERE date=$dateq");
    $newdata = json_decode($_POST['newdata'],true);
    foreach($newdata as $d){
    	$description = $db->quote($d['description']);
    	$time = $db->quote($d['time']);
    	$db->exec("INSERT INTO bells VALUES ($description,$dateq,$time)");
    }
    echo "Saved";
    header("location: ./?f=main&date=$date");
} else if($function=='applytodates'){
	$firstdate = $_POST['firstdate'];
	$lastdate = $_POST['lastdate'];
	$firstdateq = $db->quote($_POST['firstdate']);
	$lastdateq = $db->quote($_POST['lastdate']);
	$db->exec("DELETE FROM bells WHERE date>=$firstdateq AND date<=$lastdateq");
	$bells = $db->query("SELECT * FROM weeklyschedule ORDER BY time ASC")->fetchAll(PDO::FETCH_ASSOC);
	$date = strtotime($firstdate);
	$end = strtotime($lastdate);
	$i=0;
	while($date<=$end){
		$dateq = $db->quote(date('Y-m-d',$date));
		$dotw = strtolower(date('D',$date));
		foreach($bells as $b){
			if($b[$dotw]==1){
				$description = $db->quote($b['description']);
				$time = $db->quote($b['time']);
				$db->exec("INSERT INTO bells VALUES ($description,$dateq,$time)");
				$i++;
			}
		}
		$date = strtotime("+1 day",$date);	
	}
    echo "Saved";
    loadcontent($i." Bells Added to Schedule",'week');
} else if($function=='ring'){
    shell_exec("./on.sh");
    sleep(2);
    shell_exec("./off.sh");
	loadcontent("Bell Rung",$function);
} else if($function=='logout'){
    session_destroy();
    header('location: ./');
} else {
    loadcontent($function." is not coded",$function);
}












?>
