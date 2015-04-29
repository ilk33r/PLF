<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 18 15 23:23
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */
?>

<ul class="pagination pull-right">
	<?if($currentPage > 1){$pageNum=$currentPage-1;?>
	<?$link = (empty($popup)) ? $adminPath . '/' . $moduleName . '/' . $pageNum . '/' : $adminPath . '/custom/' . $popup . '/?moduleName=' . $moduleName . '&' . 'pageNumber=' . $pageNum;?>
	<li><a href="<?=$link;?>">Prev</a></li>
	<?}?>

	<?for($i = 1; $i <= $pageCount; $i++){?>
		<?if($i == $currentPage){?>
			<li><a class="active"><?=$i;?></a></li>
		<?}else{?>
			<?$link = (empty($popup)) ? $adminPath . '/' . $moduleName . '/' . $i . '/' : $adminPath . '/custom/' . $popup . '/?moduleName=' . $moduleName . '&' . 'pageNumber=' . $i;?>
			<li><a href="<?=$link;?>"><?=$i;?></a></li>
		<?}?>
	<?}?>

	<?if($currentPage < $pageCount){$pageNum=$currentPage+1;?>
		<?$link = (empty($popup)) ? $adminPath . '/' . $moduleName . '/' . $pageNum . '/' : $adminPath . '/custom/' . $popup . '/?moduleName=' . $moduleName . '&' . 'pageNumber=' . $pageNum;?>
		<li><a href="<?=$link;?>">Next</a></li>
	<?}?>
</ul>