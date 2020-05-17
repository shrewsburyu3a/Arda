<?php
function redirect($url, $statusCode = 303)
{
   header('Location: ' . $url, true, $statusCode);
   die();
}
redirect("https://medium.com/the-janus-web");
?>

