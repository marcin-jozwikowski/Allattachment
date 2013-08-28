<?php

/**
 * Nodeattachment helper
 *
 * @author Marcin Jóźwikowski <marcin@jozwikowski.pl>
 * @nodeattachment-author Juraj Jancuska <jjancuska@gmail.com>
 * @copyright (c) 2010 Juraj Jancuska
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class AllattachmentHelper extends AppHelper {

       /**
        * Used helpers
        *
        * @var array
        */
       public $helpers = array(
           'Js' => array('Jquery'),
           'Html',
           'Layout',
           'Image2',
       );

       /**
        * Attachment types
        *
        * @var array
        */
       public $attachment_types = array(
           'video',
           'audio',
           'application',
           'text',
           'image'
       );

       /**
        * Nodeattachment
        *
        * @var array
        */
       public $nodeattachment = array();

       /**
        * Before render callback
        *
        * @return void
        */
       public function beforeRender() {

              $this->conf = Configure::read('Allattachment');
       }
       
       public function categorize(&$data){
           if(isset($data['Allattachment']) && is_array($data['Allattachment'])){
               $atachmentTypes = explode(',', Configure::read('Allattachment.types'));
               foreach($data['Allattachment'] as $attachment){
                   $type = (in_array($attachment['type'], $atachmentTypes)) ? $attachment['type'] : $atachmentTypes[0];
                   if(!isset($data['Allattachment-'.$type])) $data['Allattachment-'.$type] = array();
                   $data['Allattachment-'.$type][] = $attachment;
               }
           }
           return true;
       }

       /**
        * After set node callback
        * Set all attachments by types
        *
        * @return void
        */
       public function afterSetNode() {

              foreach ($this->attachment_types as $type) {
                     $attachments[$type] = $this->extractMimeType($this->Layout->node, $type);
              }
              @$this->Layout->node['Allattachments'] = $attachments;
       }

       /**
        * Process loading image
        *
        * @param string $effect Can be: fadeIn, fadeOut, show, hide, slideIn, slideOut
        * @param string $selector Selector of load image element
        * @return string Formatted javascript code
        */
       public function requestOptions($options = array()) {
                        
              $_options = array(
                  'update' => '#attachments-listing',
                  'before' => $this->Js->get('#loading')->effect('fadeIn', array('buffer' => false)),
                  'complete' => $this->Js->get('#loading')->effect('hide', array('buffer' => false))              
              );              
              $options = Set::merge($_options, $options);              
              return $options;      
       } 

       /**
        * Node thumb
        *
        * @param string $field Filed of attachment to return
        * @return string Url of the thumb image
        */
       public function nodeThumb($width, $height, $method = 'resizeRatio', $options = array()) {

              $attachment = Set::extract('/Allattachment/.[1]', $this->Layout->node);
              if (empty($attachment)) {
                return false;
              }
              $this->setAllattachment($attachment[0]);
              if (!empty($this->allattachment)) {
                     return $this->Image2->resize($this->field('thumb_path'), $width, $height, $method, $options, FALSE, $this->field('server_thumb_path'));
              }
              return false;
       }

       /**
        * Set nodeattachment
        *
        * @param array $var
        * @return void
        */
       public function setAllattachment($attachmentData) {

              $model = 'Allattachment';
              if (isset($attachmentData['id'])) {
                     $data = $attachmentData;
              }
              if (isset($attachmentData[$model]['id'])) {
                     $data = $attachmentData[$model];
              }
              
              if (isset($data)) {
//                     $this->attachment[$model] = $data;
                     $this->allattachment = array($model => $data);
                     $this->__thumb();
                     $this->__flv();
              } else {
                     $this->allattachment = false;
              }
       }

       /**
        * If file (video) has flv variant
        *
        * @return void
        */
       private function __flv() {

              $is_video = strpos($this->field('mime_type'), 'video');
              if ($is_video === 0) {
                     $file_name = explode('.', $this->field('slug'));

                     $flv_file = $this->conf['flvDir'] . DS . $file_name[0] . '.flv';
                     if (file_exists($flv_file)) {
                            $this->setField('flv_path', '/allattachment/flv/' . $file_name[0] . '.flv'
                            );
                            return;
                     }

                     if ($file_name[1] == 'flv') {
                            $this->setField('flv_path', $this->field('path')
                            );
                     }
              }
       }

       /**
        * Thumbnails
        *
        * @param array $var
        * @return array
        */
       private function __thumb() {

              $data = $this->allattachment['Allattachment'];

              $file_type = explode('/', $data['mime_type']);
              $file_name = explode('.', $data['slug']);

              // image
              if ($file_type[0] == 'image') {
                     $data['thumb_path'] = $data['path'];
                     $data['server_thumb_path'] = ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . $data['path'];
                     $this->allattachment['Allattachment'] = $data;
                     return;
              }

              // check if exists thumb with original filename
              $thumb_filename = $file_name[0] . '.' . Configure::read('Allattachment.thumbnailExt');
              if (file_exists($this->conf['thumbDir'] . DS . $thumb_filename)) {
                     $data['thumb_path'] = '/allattachment/img/tn/' . $thumb_filename;
                     $data['server_thumb_path'] = $this->conf['thumbDir'] . DS . $thumb_filename;
                     $this->allattachment['Allattachment'] = $data;
                     return;
              }

              // check if exists thumb with mime type filename
              $thumb_filename = 'thumb_' . $file_type[0] . '.' . Configure::read('Allattachment.thumbnailExt');
              if (file_exists($this->conf['iconDir'] . DS . $thumb_filename)) {
                     $data['thumb_path'] = '/allattachment/img/' . $thumb_filename;
                     $data['server_thumb_path'] = $this->conf['iconDir'] . DS . $thumb_filename;
                     $this->allattachment['Allattachment'] = $data;
                     return;
              } else {
                     $data['thumb_path'] = '/allattachment/img/thumb_default.' . $this->conf['thumbExt'];
                     $data['server_thumb_path'] = $data['thumb_path'] . DS .
                             'thumb_default.' . $this->conf['thumbExt'];
                     $this->allattachment['Allattachment'] = $data;
                     return;
              }
       }

       /**
        * Get field from nodeattachment data
        *
        * @param string $field
        * @return void
        */
       public function field($field_name = 'id') {

              $model = 'Allattachment';
              if (isset($this->allattachment[$model][$field_name])) {
                     return $this->allattachment[$model][$field_name];
              } else {
                     return false;
              }
       }

       /**
        * Set nodeattachment field
        *
        * @param string $field_name
        * @param void $value
        * @return boolean
        */
       public function setField($field_name, $value) {

              $model = 'Allattachment';
              $this->allattachment[$model][$field_name] = $value;
       }

       /**
        * Extract mime types from
        *
        * @param string $type Mime type
        * @return array
        */
       public function filterMime($type = 'image') {

              return $this->extractMimeType($this->Layout->node, $type);
       }

       /**
        * Extract type of attachment
        *
        * @param string $type
        * @return array
        */
       public function filterType($type = 'Gallery') {

              $attachments = Set::extract('/Allattachment[type=/' . $type . '/]', $this->Layout->node);
              return $attachments;
       }

       /**
        * DEPRECATED!!!  use filterMime instead
        * Get attachments
        *
        * @param string $type mime type
        * @return array
        */
       public function getAllattachments($type = 'image') {

              return $this->Layout->node['Allattachments'][$type];
       }

       /**
        * Extract mime types
        *
        * @param array $node
        * @param string $type Mime Type to extract
        * @return array
        */
       private function extractMimeType($node, $type = 'image') {
              $attachments = Set::extract('/Allattachment[mime_type=/' . $type . '(.*)/]', $node);
              return $attachments;
       }

}
