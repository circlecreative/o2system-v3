<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-body">
                <div class="form-horizontal form-groups-bordered">
                <?php foreach($contents as $content): ?>
                    <?php if(element('fields',$content)): ?>
                        <?php foreach($content['fields'] as $field): ?>
                            <div class="form-group">
                                <div class="col-sm-2 align-right">
                                    <?php echo $field['label']; ?>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo $field['input']; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>