<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'U3ADatabase.php';

$name = isset($_POST["name"]) ? $_POST["name"] : (isset($_GET["name"]) ? $_GET["name"] : "ShrewsburyU3A slideshow");
$header = isset($_POST["header"]) ? $_POST["header"] : (isset($_GET["header"]) ? $_GET["header"] : $name);
$groups_id = isset($_POST["group"]) ? $_POST["group"] : (isset($_GET["group"]) ? $_GET["group"] : 0);
$attach = isset($_POST["ids"]) ? $_POST["ids"] : (isset($_GET["ids"]) ? $_GET["ids"] : null);
$alltitles = isset($_POST["titles"]) ? $_POST["titles"] : (isset($_GET["titles"]) ? titles["ids"] : null);
if ($attach)
{
	$urls = explode(",", $attach);
}
else
{
	$urls = [];
}
if ($alltitles)
{
	$titles = explode("^", $alltitles);
}
else
{
	$titles = [];
}
//var_dump($ids);
?>
<!doctype html>
<html>
	<head>
		<title><?php echo $header; ?></title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
		<script src="galleria/galleria.min.js"></script>
		<link type="text/css" rel="stylesheet" href="galleria/themes/twelve/galleria.twelve.css">
		<style>
			.galleria
			{
				width:70vw;
				height:50vh;
				margin-top:15vh;
				margin-left:15vw;
			}
		</style>
	</head>
	<body>
		<h2><?php echo $name; ?></h2>
		<div class="galleria">
			<?php
			for ($n = 0; $n < count($urls); $n++)
			{
				?>
				<img src="<?php echo $urls[$n]; ?>" data-title="<?php echo $titles[$n]; ?>"/>
				<?php
			}
			?>
		</div>
		<script>(
					  function ()
					  {
						  Galleria.loadTheme("galleria/themes/twelve/galleria.twelve.min.js");
						  Galleria.run(".galleria",
									 {
										 trueFullscreen: true,
										 fullscreenCrop: false,
										 responsive: true,
										 height: 0.5,
										 maxScaleRatio: 1,
										 extend: function ()
										 {
											 this.attachKeyboard({
												 left: this.prev,
												 right: this.next
											 });
										 }
									 });
					  }());
		</script>
	</body>
</html>

