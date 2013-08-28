<div id="storage-uploader">
<?php
$upload_dir = Configure::read('Allattachment.storageUploadDir');
if (!empty($upload_dir)) {
        echo $this->Html->link(__('Attach from server', true), Router::url(array(
                    'controller' => 'allattachment',
                    'action' => 'addStorageFile',
                    'plugin' => 'allattachment',
                    'owner_id' => $this->data['Node']['id']), true) . '?KeepThis=true&TB_iframe=true&height=400&width=600',
                array(
                    'class' => 'thickbox'));
}
?>
</div>

<div id="file-uploader">
    <noscript>
        <p>Please enable JavaScript to use file uploader.</p>
        <!-- or put a simple form for upload here -->
    </noscript>
</div>
<?php $this->Form->unlockField('file'); ?>


<div id="loading" style="display:none;">
        <?php echo $this->Html->image('/Allattachment/img/loading-big.gif', array('alt' => 'Loader'));?>
</div>
<div id="attachments-listing">

</div>
<?php
       $this->append('css', $this->Html->css('Allattachment.admin.css'));
       $this->append('scriptBottom', $this->Html->script('/Allattachment/js/valums-file-uploader/client/fileuploader.js'));
//       debug($this->request); debug($this->data);
       $currentModelName = Inflector::camelize(Inflector::singularize($this->request->params['controller']));
//       debug($currentModelName); die;
        $owner_id = $this->data[$currentModelName]['id'];
        $owner = $this->request->params['plugin'].'.'.$this->request->params['controller'];
        // vars for javacript
        $action_url = $this->Html->url(array(
            'controller' => 'allattachment',
            'action' => 'add',
            'plugin' => 'allattachment',
            'owner' => $owner,
            'owner_id' => $owner_id
        ));
        $ownerIndex_url = $this->Html->url(array(
            'plugin' => 'allattachment',
            'controller' => 'allattachment',
            'action' => 'allattachmentIndex',
            'owner' => $owner,
            'owner_id' => $owner_id, 
            md5(rand(1, 1000))
        ));
        
?>
<script type="text/javascript">

function refreshListing() {
        $('#loading').show();
        $('#attachments-listing').load('<?php echo $ownerIndex_url; ?>', function(response, status, xhr) {
                $('#loading').hide();
        });
}

$(document).ready(function() {

        refreshListing();

        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader'),
            action: '<?php echo $action_url;?>',
            debug: true,
            params: {
                    //owner_id: '<?php echo $owner_id;?>',
                    //owner: '<?php echo $owner; ?>'
            },
            onSubmit: function(id, fileName){
                $('#loading').show();
            },
            onComplete: function(id, fileName, responseJSON){                                        
                    refreshListing();
            }
        });
   

});
</script>