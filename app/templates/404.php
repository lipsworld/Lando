<?php 
header("HTTP/1.1 404 Not Found");

$url_source = "typed";

if(isset($_SERVER['HTTP_REFERER'])) {
	$url_source = "external";

	if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) >= 0)
		$url_source = "internal";
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>404 (Page Not Found) - <?php echo $site_title ?></title>
  <style>
  	body, input {
  		font: 16px/1.4 "Helvetica Neue", helvetica, arial, sans-serif;
  		color: #222;
  	}

  	body {
  		background-color: #eee;
  	}

  	#wrapper {
  		padding: 1px 20px 10px;
  		width: 600px;
  		margin: 50px auto;
  		background-color: white;
  		box-shadow: 0 0 10px rgba(0,0,0,0.1);
  	}

  	ul {
  		list-style: none;
  		padding: 0;
  	}

  	input {
  		padding: 0 0.5ex;
  	}
  </style>
</head>
<body>
<div id="wrapper">

	<h1>404, Page Not Found</h1>
	<div>
		<p>Sorry, but the page you were trying to view doesn't exist.</p>

		<p>
		<?php if($url_source == "typed"): ?>
		It looks like you might have mistyped the URL. Please check it and try again.

		<?php elseif($url_source == "external"): ?>
		It looks like you arrived here from another site or search engine, possibly via an out-of-date page. If you can, please let them know they have a broken link pointing here.

		<?php elseif($url_source == "internal"): ?>
		It looks like there's a broken link on the site. Sorry about that! Please let us know how you got here and we'll get it fixed.
		<?php endif ?>

		<br />Alternatively, you can try going back to <a href="<?php echo $site_root ?>">the homepage</a>.
		</p>
	</div>
    
	<script>
		var GOOG_FIXURL_LANG = (navigator.language || '').slice(0,2),
		GOOG_FIXURL_SITE = location.host;
	</script>
	<script src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>

</div><!-- #wrapper -->
</body>
</html>