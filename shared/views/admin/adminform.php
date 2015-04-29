<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
 * ------------------------------------------------
 * Admin
 * ------------------------------------------------
 *
 * @package		shared
 * @createdate	Apr 19 15 00:28
 * @version		1.0.0
 * @author		ilker ozcan
 *
 */

?>

<form autocomplete="off" role="form" method="post" action="<?=$moduleAction;?>" class="adminForm" enctype="multipart/form-data">

	<?foreach($formFields as $field){?>
	<div class="form-group <?=($field->hasError)?'has-error':'';?>">
		<label for="<?=$field->name;?>"><?=$field->localizeName;?></label>
		<?=$field->field;?>
		<?if($field->hasError){?>
		<span class="glyphicon glyphicon-warning-sign form-control-feedback" aria-hidden="true"></span>
		<?}?>
	</div>
	<?}?>
	<input type="hidden" name="plf-csrf-token" value="<?=$csrfToken;?>">
	<button type="submit" class="btn btn-default pull-right">Save</button>
	<div class="clearfix"></div>
</form>