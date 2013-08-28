<?php
echo $html->css('admin');
echo $html->script('jquery/jquery.min');
?>
<script type="text/javascript">
    $(document).ready(function() {
        parent.refreshListing();
    });
</script>

<?php
if (!empty($notice)) {
        echo $this->Html->div($notice['class'], $notice['text'], array(
            'id' => 'flashMessage')
        );
}
?>
<div>
        <ul>
                <?php
                foreach ($content['1'] AS $file) {  ?>
                        <li>
                                <strong>
                                <?php
                                        echo $this->Html->link($file, array(
                                            'plugin' => 'allattachment',
                                            'controller' => 'allattachment',
                                            'action' => 'addStorageFile',
                                            'file' => $file,
                                            'owner_id' => $owner_id,
                                            'owner' => $owner
                                        ));
                                ?>
                                </strong>&nbsp;
                                <?php
                                        echo $this->Html->link(__('Delete', true), array(
                                            'plugin' => 'allattachment',
                                            'controller' => 'allattachment',
                                            'action' => 'deleteStorageFile',
                                            'file' => $file,
                                            'owner_id' => $owner_id,
                                            'owner' => $owner
                                        ));
                                ?>

                        </li>
                <?php } ?>
        </ul>
</div>