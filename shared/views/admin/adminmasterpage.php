<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
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
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Administration Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Administration Page">
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

</head>

<body>
<div class="container-fluid adminContainer">
	<div class="row clearfix adminHeader">
		<div class="col-md-12 column"><h2>Administration Page</h2></div>
	</div>
	<div class="row clearfix adminPageRow">
		<?if(isset($leftMenu)){?>
		<div class="col-md-2 column menuArea">
			<a class="changePasswordLink" href="<?=Adminconfig::$adminPath . '/custom/changePassword/';?>"><i class="glyphicon glyphicon-wrench" aria-hidden="true"></i>Welcome, <?=$userName;?></a>
			<?$currentGroup = '';foreach($leftMenu as $menuData){?>
				<?if($currentGroup != $menuData->name){$currentGroup=$menuData->name;?>
					<span class="label menuGroup"><i class="glyphicon <?=$menuData->icon;?>" aria-hidden="true"></i><?=$menuData->localizedName;?></span>
				<?}?>
				<ul>
					<?foreach($menuData->URLList as $urlHref => $urlName){?>
						<li><a href="<?=$urlHref;?>/" class="text-anchor"><?=$urlName;?></a></li>
					<?}?>
				</ul>
			<?}?>
		</div>
		<?}?>
		<div class="col-md-10 column contentArea">
			<?=(isset($breadcrumb))?$breadcrumb:'';?>
			<?if($error){?>
				<div class="alert alert-dismissable alert-<?=$errorData->type;?>">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h4><?=$errorData->title;?></h4>
					<?=$errorData->message;?>
				</div>
			<?}?>
			<?=(isset($content))?$content:'';?>
			<?=(isset($pagination))?$pagination:'';?>
		</div>
	</div>
</div>

	<script type="text/javascript" src="<?=$contentPath;?>js/jquery.min.js"></script>
	<script type="text/javascript" src="<?=$contentPath;?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?=$contentPath;?>js/scripts.js"></script>

	<?if(isset($customCss)){if(!empty($customCss)){?>
		<link href="<?=$customCss;?>" rel="stylesheet">
	<?}}?>

	<?if(isset($customJs)){if(!empty($customJs)){?>
		<script type="text/javascript" src="<?=$customJs;?>"></script>
	<?}}?>

</body>
</html>
