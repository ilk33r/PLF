<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 18 15 20:15
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

?>
<table class="table table-hover">
	<thead>
		<tr>
			<?foreach($theads as $thead){?>
				<th><?=$thead;?></th>
			<?}?>
		</tr>
	</thead>
	<tbody>
		<?$row=0;foreach($tbodies as $tbody){?>
			<tr class="<?=($row % 2 == 0) ? '' : 'active';?>">
				<?foreach($tbody as $tbodyContent){?>
					<td><?=$tbodyContent;?></td>
				<?}?>
			</tr>
		<?$row++;}?>
	</tbody>
</table>
