<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 16 15 23:00
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

?>

<ul class="breadcrumb">
	<?$i=0;foreach($breadcrumbs as $breadcrumbLink => $breadcrumbName) {?>
		<?if($i != count($breadcrumbs) - 1){?>
			<li><a href="<?=$breadcrumbLink;?>"><?=$breadcrumbName;?></a><span class="divider"></span></li>
		<?}else{?>
			<li class="active"><?=$breadcrumbName;?></li>
		<?}?>
	<?$i++;}?>
</ul>