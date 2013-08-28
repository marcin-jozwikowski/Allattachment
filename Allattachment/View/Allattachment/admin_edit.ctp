<div class="attachments form">
       <div class="actions">
                            <?php
                            echo $this->Js->link(__('<< Back to listing', true), 
                                    array(
                                        'plugin' => 'allattachment',
                                        'controller' => 'allattachment',
                                        'action' => 'allattachmentIndex',
                                        $this->data['Allattachment']['owner_id'],
                                        'owner' => $this->request->params['named']['owner'],
                                        'owner_id' => $this->request->params['named']['owner_id'],
                                    ),
                                     array_merge(array('class'=>'btn btn-danger'), $this->Allattachment->requestOptions())
                            );                            
                            ?>

       </div>
       <?php echo $this->Form->create('Allattachment'); ?>
       <fieldset>

              <div id="node-basic">
                     <div class="thumbnail">
                            <?php
                            $this->Allattachment->setAllattachment($this->data);

                            echo $this->Image2->resize(
                                    $this->Allattachment->field('thumb_path'), 
                                    140, 140, 'resizeRatio', 
                                    array('alt' => $this->Allattachment->field('slug')), 
                                    false, 
                                    $this->Allattachment->field('server_thumb_path')
                            );
                            ?>
                     </div>
                     <?php 
                        echo $this->Html->tag('div',
                          $this->Js->submit(__('Save attachment', true), array_merge(array('class'=>'btn btn-primary'), $this->Allattachment->requestOptions())),
                          array('class'=>'input text')
                        );
                     ?>
                     <?php
                     echo $this->Form->input('id');
                     echo $this->Form->input('title', array('label' => __('Title', true)));
                     echo $this->Form->input('description', array('label' => __('Description', true)));
                     echo $this->Form->input('type', array('label' => __('Category', true)));
                     echo $this->Form->input('author', array('label' => __('Author', true)));
                     echo $this->Form->input('author_url', array('label' => __('Author Url', true)));
                     echo $this->Form->input('status', array('label' => __('Status', true)));
                     echo $this->Form->hidden('owner_id');
                     echo $this->Form->hidden('mime_type');
                     echo $this->Form->hidden('slug');
                     ?>
              </div>

              <div id="node-info">
                     <?php
                     echo $this->Form->input('file_url', array('label' => __('File URL', true), 'value' => Router::url($this->data['Allattachment']['path'], true), 'readonly' => 'readonly'));
                     echo $this->Form->input('file_type', array('label' => __('Mime Type', true), 'value' => $this->data['Allattachment']['mime_type'], 'readonly' => 'readonly'));
                     ?>
              </div>
       </fieldset>
       <?php
       echo $this->Js->submit(__('Save attachment', true), array_merge(array('class'=>'btn btn-primary'), $this->Allattachment->requestOptions()));
       
       
       echo $this->Js->writeBuffer();
       
       
       /* echo $this->Ajax->submit(__('Save attachment', true), array(
         'url' => array(
         'plugin' => 'nodeattachment',
         'controller' => 'nodeattachment',
         'action' => 'edit',
         $this->data['Nodeattachment']['id']),
         'update' => 'attachments-listing',
         'indicator' => 'loading')); */
       ?>
</div>