<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Adminlogin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 16 15 20:02
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

?>
<!DOCTYPE html>
<html lang="en" class="loginform">
<head>
	<meta charset="utf-8">
	<title>Login Administration Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Login Administration Page">
	<meta name="author" content="PLF (Lightweight PHP web framework)">

	<!--link rel="stylesheet/less" href="less/bootstrap.less" type="text/css" /-->
	<!--link rel="stylesheet/less" href="less/responsive.less" type="text/css" /-->
	<!--script src="js/less-1.3.3.min.js"></script-->
	<!--append ‚Äò#!watch‚Äô to the browser URL, then refresh the page. -->

	<link href="<?=$contentPath;?>css/bootstrap.min.css" rel="stylesheet">
	<link href="<?=$contentPath;?>css/style.css" rel="stylesheet">

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="<?=$contentPath;?>js/html5shiv.js"></script>
	<![endif]-->

	<!-- Fav and touch icons -->
	<link rel="shortcut icon" href="<?=$contentPath;?>img/favicon.png">

	<script type="text/javascript" src="<?=$contentPath;?>js/jquery.min.js"></script>
	<script type="text/javascript" src="<?=$contentPath;?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?=$contentPath;?>js/scripts.js"></script>
</head>

<body class="loginform">
<div class="container-fluid loginform">
	<div class="row clearfix">
		<div class="col-md-4 column">
		</div>
		<div class="col-md-4 column formColumn">
			<div class="formArea">
				<div class="verticalCenter">
					<?if($error){?>
					<p class="label-danger errorMessageArea"><?=(isset($errorMessage))?$errorMessage:'';?></p>
					<?}?>
					<form class="form-horizontal" role="form" method="post" action="<?=$formAction;?>" autocomplete="on">
						<div class="form-group">
							<label for="inputEmail3" class="col-sm-2 control-label">Username</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="inputEmail3" name="username">
							</div>
						</div>
						<div class="form-group">
							<label for="inputPassword3" class="col-sm-2 control-label">Password</label>
							<div class="col-sm-10">
								<input type="password" class="form-control" id="inputPassword3" name="password">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<button type="submit" class="btn btn-default">Log in</button>
							</div>
						</div>
						<input type="hidden" name="plf-csrf-token" value="<?=$csrfToken;?>">
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4 column">
		</div>
	</div>
</div>
</body>
</html>
