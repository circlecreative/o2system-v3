<div class="row">
	<div class="col-md-12">
		<div class="panel-group joined" id="accordion-<?php echo element('id',$form['attr']); ?>">
			<?php foreach($selectors as $key => $selector): ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion-<?php echo element('id',$form['attr']); ?>" href="#content-<?php echo alias($selector); ?>">
							<?php echo $selector; ?>
						</a>
					</div>
				</div>
				<?php $content = $contents[$key+1]; ?>
				<div id="<?php echo element('id',$content['attr']); ?>" class="panel-collapse collapse <?php echo ($key == 0 ? 'in' : ''); ?>">
					<div class="panel-body">
						<?php if(element('fields',$content)): ?>
							<?php foreach($content['fields'] as $field): ?>
								<?php if($field['label'] == ''): ?>
					            	<?php echo $field['input']; ?>
					            <?php else: ?>
					            	<div id="<?php echo $field['container']; ?>" class="form-group">
					            		<label class="col-sm-3 control-label"><?php echo $field['label']; ?></label>
					                    <div class="col-sm-7">
					                        <?php echo $field['input']; ?>
					                    </div>
					                </div>
					            <?php endif; ?>
					            <?php if(element('tabs', $content)): ?>
						        	<div id="<?php echo $field['container']; ?>" class="form-group">
							        	<?php foreach($content['tabs'] as $tab): ?>
							        	<div class="panel-group col-sm-10" id="<?php echo $content['attr']['id'].'-accordion'; ?>">
								        	<?php $tab_key = 0; foreach($tab['contents'] as $tab_content): ?>
						                    	<?php $tab_content['attr']['id'] = str_replace('content', $content['attr']['id'],$tab_content['attr']['id']); ?>
						                    	<?php $tab_content['attr']['class'] = 'tab-pane '.$tab_content['attr']['class']; ?>
						                    	<div class="panel">
						                    		<div class="panel-heading">
					                                    <h4 class="form-header panel-title">
					                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#<?php echo $content['attr']['id'].'-accordion'; ?>" href="#<?php echo $tab_content['attr']['id']; ?>">
					                                            <?php echo $tab['selectors'][$tab_key++]; ?>
					                                        </a>
					                                    </h4>
					                                </div>
					                                <div id="<?php echo $tab_content['attr']['id']; ?>" class="panel-collapse collapse in" style="height: auto;">
					                                    <div class="panel-body">
					                                        <?php foreach($tab_content['fields'] as $tab_field): ?>
											            	<div id="<?php echo $field['container']; ?>" class="form-group">
											            		<div class="col-sm-3 align-right">
											            			<?php echo $tab_field['label']; ?>
											            		</div>
											                    <div class="col-sm-7">
											                        <?php echo $tab_field['input']; ?>
											                    </div>
											                </div>
											            <?php endforeach; ?>
					                                    </div>
					                                </div>
										    	</div>
									    	<?php endforeach; ?>
				                        </div>
						            	<?php endforeach; ?>
						            	<div class="clearfix"></div>
						            </div>
					            <?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>