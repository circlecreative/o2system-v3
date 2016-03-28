<div id="form-main" class="col-xs-9 col-sm-9 col-md-9 col-lg-9 form-panel-group">
	<?php if( ! empty( $fieldsets->main ) ): ?>
		<?php foreach($fieldsets->main as $fieldset): ?>
			<?php echo $fieldset; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<div id="form-sidebar" class="col-xs-3 col-sm-3 col-md-3 col-lg-3 form-panel-group">
	<?php if( ! empty( $fieldsets->sidebar ) ): ?>
		<?php foreach($fieldsets->sidebar as $fieldset): ?>
			<?php echo $fieldset; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
