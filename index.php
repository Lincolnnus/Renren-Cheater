<?php 
   $pageSize = 100;
   $app_id = "89c1c88a16e64c0285a2559ee7030896";
   $app_secret = "f19442c83af04721a9760dd447663049";

   $my_url = "http://apps.renren.com/renrenzbq/index.php";//Please configure your app accordingly
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
     echo '<script> var totalFriends = '.$numberOfFriends.'; var friends = new Array('.$numberOfFriends.');';
     for($i = 0; $i < $totalPage; $i++){  
        $graph_url = "https://api.renren.com/v2/user/friend/list?access_token=" 
       . $params->access_token."&userId=".$params->user->id."&pageSize=".$pageSize."&pageNumber=".($i+1);
        $friends = json_decode(file_get_contents($graph_url));

        for($j = 0; $j < $pageSize; $j++){
          if($j<count($friends->response)){
            echo 'friends['.($i*$pageSize+$j).']='.$friends->response[$j]->id.';';
         }
        }
     }
     echo '</script>';
   }
   else {
     echo("The state does not match. You may be a victim of CSRF.");
   }
 ?>
<html>
<head>
  <script>
     function visit(){
      var numFriendsToVisit = document.getElementById("theValue").value;
      for(var i=0;i<numFriendsToVisit;i++){
        var friend=friends[Math.floor(Math.random() * totalFriends)];
        var ni = document.getElementById('myDiv');
        var newdiv = document.createElement("div"); 
        newdiv.innerHTML ='Friend ' +friend+'has been visited!';
        ni.appendChild(newdiv);
        window.open('http://renren.com/'+friend);
      }
    }
  </script>
</head>
<body>
  <div id="myDiv"></div>
  Number of Friends to Visit <input id="theValue"/>
  <button onclick="visit();">One Click Visit</button>
</body>
</html>