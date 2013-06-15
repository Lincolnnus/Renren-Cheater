<?php 
   $numberOfFriends = 2; // Total Number of Friends (Be Reminded that the Total Procedure takes $numberOfFriends*$timeInterval Time)
   $timeInterval = 5;//Time Interval for Visiting New Friend(second)
   $app_id = "89c1c88a16e64c0285a2559ee7030896";
   $app_secret = "f19442c83af04721a9760dd447663049";

   $my_url = "http://localhost/zbq/index.php";//Please configure your app accordingly
   $grant_type="authorization_code";

   session_start();
   $code = $_REQUEST["code"];

   if(empty($code)) {
     $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
     $dialog_url = "http://graph.renren.com/oauth/authorize?client_id=" 
       . $app_id."&response_type=code" ."&redirect_uri=" . urlencode($my_url) . "&state="
       . $_SESSION['state'];

     echo("<script> top.location.href='" . $dialog_url . "'</script>");
   }

   if($_REQUEST['state'] == $_SESSION['state']) {
     $token_url = "https://graph.renren.com/oauth/token?"
       . "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
       . "&client_secret=" . $app_secret ."&grant_type=" . $grant_type . "&code=" . $code;
     $response = @file_get_contents($token_url);
     $params = json_decode($response);
     $graph_url = "https://api.renren.com/v2/user/friend/list?access_token=" 
       . $params->access_token."&userId=".$params->user->id."&pageSize=".$numberOfFriends."&pageNumber=1";
     $friends = json_decode(file_get_contents($graph_url));
     for($i = 0; $i < $numberOfFriends; $i++){
        echo("Visited Renren ID ".$friends->response[$i]->id);
        echo '<script>window.open("http://renren.com/'.$friends->response[$i]->id.'", "_blank", "width=400,height=500")</script>';
        sleep($timeInterval);
     }
   }
   else {
     echo("The state does not match. You may be a victim of CSRF.");
   }

 ?>