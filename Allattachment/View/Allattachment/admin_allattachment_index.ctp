<div class="attachments index">

       <table cellpadding="0" cellspacing="0" class="table table-striped">
              <tbody id="sortable">
                     <?php
                     foreach ($allattachments AS $attachment) {
                            $this->Allattachment->setAllattachment($attachment);
                            $file_name = explode('.', $this->Allattachment->field('slug'));

                            $actions = $this->Js->link(__('Edit', true), 
                                    array(
                                        'plugin' => 'allattachment',
                                        'controller' => 'allattachment',
                                        'action' => 'edit',
                                        $attachment['Allattachment']['id'],
                                        'owner' => $this->request->params['named']['owner'],
                                        'owner_id' => $this->request->params['named']['owner_id'],
                                    ),
                                    array_merge(array('class'=>'btn btn-primary'),$this->Allattachment->requestOptions())
                            );
                            $actions .= '&nbsp;';
                            $actions .= $this->Js->link(__('Delete', true),
                                    array(
                                        'plugin' => 'allattachment',
                                        'controller' => 'allattachment',
                                        'action' => 'delete',
                                        $attachment['Allattachment']['id'],
                                        'token' => $this->params['_Token']['key'],
                                        'owner' => $this->request->params['named']['owner'],
                                        'owner_id' => $this->request->params['named']['owner_id'],
                                    ),
                                    array_merge(array('class'=>'btn btn-danger'), $this->Allattachment->requestOptions(
                                            array(
                                                'confirm' => __('Are you sure?', true),
                                                'method' => 'POST'
                                            )
                                    ))
                            );
                              
                            $thumbnail = $this->Image2->resize(
                                    $this->Allattachment->field('thumb_path'), 75, 75, 'resizeRatio', array('alt' => $this->Allattachment->field('slug')), false, $this->Allattachment->field('server_thumb_path'));

                            $idrow = $attachment['Allattachment']['id'] . '<br />';
                            $class = ($attachment['Allattachment']['status']) ? 'icon-ok green' : 'icon-remove red';
                            $idrow .= $this->Html->tag('span', '', array('class'=>$class));
                            $row = '';
                            $row .= $this->Html->tag('td', $this->Html->tag('span', '', array('class' => 'ui-icon ui-icon-arrowthick-2-n-s')));
                            $row .= $this->Html->tag('td', $idrow);
                            $row .= $this->Html->tag('td', $thumbnail);
                            $row .= $this->Html->tag('td', '(' . $file_name[1] . ')');
                            $row .= $this->Html->tag('td', $attachment['Allattachment']['title']);
                            $row .= $this->Html->tag('td', $actions);
                            echo $this->Html->tag(
                                    'tr', $row, array('class' => 'ui-state-default', 'id' => 'attachments_' . $attachment['Allattachment']['id'])
                            );
                     }
                     ?>
              </tbody>
       </table>
       <?php
       // sort attachments
       $sortUrl = $this->Html->url(array(
           'plugin' => 'allattachment',
           'controller' => 'allattachment',
           'action' => 'sort'
               ));
       $options = array(
           "complete" => "$.post('" . $sortUrl . "', $('#sortable').sortable('serialize'))"
       );
       $this->Js->get('#sortable');
       $this->Js->sortable($options);

       echo $this->Js->writeBuffer();
       ?>
</div>

