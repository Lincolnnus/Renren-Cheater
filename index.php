<?php 
   $pageSize = 100;
   $app_id = "89c1c88a16e64c0285a2559ee7030896";
   $app_secret = "f19442c83af04721a9760dd447663049";

   $my_url = "http://localhost/zbq/index.php";//Please configure your app accordingly
   $grant_type="authorization_code";

   session_start();
   $code = $_REQUEST["code"];

   if(empty($code)) {
     $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
     $dialog_url = "http://graph.renren.com/oauth/authorize?client_id=" 
       . $app_id."&response_type=code&scope=" ."&redirect_uri=" . urlencode($my_url) . "&state="
       . $_SESSION['state'];

     echo("<script> top.location.href='" . $dialog_url . "'</script>");
   }

   if($_REQUEST['state'] == $_SESSION['state']) {
     $token_url = "https://graph.renren.com/oauth/token?"
       . "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
       . "&client_secret=" . $app_secret ."&grant_type=" . $grant_type . "&code=" . $code;
     $response = @file_get_contents($token_url);
     $params = json_decode($response);

     $profile_url="https://api.renren.com/v2/profile/get?access_token=".$params->access_token."&userId=".$params->user->id;
     $response = @file_get_contents($profile_url);
     $userInfor = json_decode($response);
     $numberOfFriends = $userInfor->response->friendCount;

     $totalPage = ceil($numberOfFriends/$pageSize);
     echo '<html><head><script> function visit(){';
     echo "var ni = document.getElementById('myDiv');";
     for($i = 0; $i < $totalPage; $i++){  
        $graph_url = "https://api.renren.com/v2/user/friend/list?access_token=" 
       . $params->access_token."&userId=".$params->user->id."&pageSize=".$pageSize."&pageNumber=".($i+1);
        $friends = json_decode(file_get_contents($graph_url));

        if ((count($friends->response)) < $pageSize) 
          $pageSize = count($friends->response);
        for($j = 0; $j < $pageSize; $j++){
          echo 'var newdiv = document.createElement("div"); newdiv.innerHTML = "Friend '.$friends->response[$j]->id.' has been visited!"; ni.appendChild(newdiv);';
          echo 'window.open("http://renren.com/'.$friends->response[$j]->id.'");';
        }
     }
     echo '}</script></head>';
     echo '<body><input type="hidden" value="0" id="theValue"/><div id="myDiv"></div><button onclick="visit();">One Click Visit</button></body></html>';
   }
   else {
     echo("The state does not match. You may be a victim of CSRF.");
   }
 ?>