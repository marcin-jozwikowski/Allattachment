<?php

/**
 * Description of attachments_controller
 *
 * @package  Croogo
 * @author Marcin Jóźwikowski <marcin@jozwikowski.pl>
 * @nodeattachment-author Juraj Jancuska <jjancuska@gmail.com>
 */
class AllattachmentController extends AllattachmentAppController {

       /**
        * Controller Name
        *
        * @var string
        */
       public $name = 'Allattachment';

       /**
        * Used helpers
        *
        * @var array
        */
       public $helpers = array(
           'Js' => array('Jquery'),
           'Text',
           'Image',
           'FileManager.FileManager',   
       );

       /**
        * Used Models
        *
        * @var array
        **/
       public $uses = array(
              'Allattachment.Allattachment',
//              'Node',
              'Term');
       
       /**
        * Used components
        *
        * @var array
        */
       public $components = array(
       );       
       
       /**
        * uoload dir
        *
        * @var string
        */
       public $uploads_dir = 'uploads';

       /**
        * Before filter callback,
        * disable CSFR security check to avoid security error
        *
        * @return void
        */
       function beforeFilter() {

              parent::beforeFilter();
              $this->Security->validatePost = false;
              $this->Security->csrfCheck = false;

              $this->uploads_path = WWW_ROOT . $this->uploads_dir;

              $cfg = Configure::read('Allattachment');
              $types = explode(',', $cfg['types']);
              $types = array_combine($types, $types);
              $this->set(compact('types'));
       }

       /**
        * Node attachment index
        *
        * @param  integer $id Node id
        * @return void
        */
       public function admin_index($id) {

              $this->set('title_for_layout', __('Attachments', true));
       }

       /**
        * Node attachment index
        *
        * @param  integer $id Node id
        * @return void
        */
       public function admin_allattachmentIndex($owner = null, $owner_id = null) {
                $owner = $this->request->params['named']['owner'];
                $owner_id = $this->request->params['named']['owner_id'];
              $this->set('title_for_layout', __('Attachments', true));

              if (!$owner_id || !$owner) {
                     
              } else {
                  $this->Allattachment->recursive = 0;
                $allattachments = $this->Allattachment->find('all', array(
                    'conditions' => array('owner_id' => $owner_id, 'owner'=>$owner),
                    'order' => array('priority ASC', 'created ASC')
                        ));
                $this->set(compact('allattachments'));
                $this->disableCache();
              }
       }

       /**
        * Upload attachment
        *
        * @return void
        */
       public function admin_add() {
              Configure::write('debug', 0);
              $this->disableCache();
              $this->autoLayout = false;
              $this->autoRender = false;
              
              $ownerId = $this->request->params['named']['owner_id'];
              $owner = $this->request->params['named']['owner'];

              $allowedExtensions = explode(',', Configure::read('Allattachment.allowedFileTypes'));
              $sizeLimit = Configure::read('Allattachment.maxFileSize') * 1024 * 1024;
              App::import('Vendor', 'Allattachment.fileuploader');
              $Uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
              $result = $Uploader->handleUpload($this->uploads_path . DS);
              $uploadedFile = $Uploader->getFilename();
              if (!empty($ownerId) && !empty($owner) && ($uploadedFile != false)) {

                     $fileName = Inflector::slug($uploadedFile['filename']);
                     $fileName .= '.' . $uploadedFile['ext'];
                     $fileName = $this->__uniqeSlugableFilename($fileName);

                     $uploadPath = $this->uploads_path . DS .
                             $uploadedFile['filename'] . '.' . $uploadedFile['ext'];
                     $newPath = $this->uploads_path . DS . $fileName;
                     rename($uploadPath, $newPath);

                     $data = array(
                         'owner' => $owner,
                         'owner_id' => $ownerId,
                         'slug' => $fileName,
                         'path' => '/' . $this->uploads_dir . '/' . $fileName,
                         'title' => $uploadedFile['filename'],
                         'status' => 1,
                         'mime_type' => $this->__getMime($newPath)
                     );
                     
                     if (!$this->Allattachment->save($data, false)) {
                            $result = array('success' => false);
                     }
              }
              echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
              
       }

       /**
        * Attach storage file
        *
        * @param string $file_path
        * @return void
        */
       public function admin_addStorageFile() {

//              App::uses('Folder', 'Utility');

              $this->layout = 'ajax';
              $notice = array();

              $storage_path = Configure::read('Allattachment.storageUploadDir');

              if (empty($storage_path) || empty($this->params['named']['owner_id']) || empty($this->params['named']['owner'])) {
                     $this->cakeError('error404');
              }

              $owner = $this->params['named']['owner'];
              $ownerId = $this->params['named']['owner_id'];

              if (!empty($this->params['named']['file'])) {
                    App::uses('File', 'Utility');
                    $bigFilePath = $storage_path . DS . $this->params['named']['file'];
                     $File = new File($bigFilePath);

                     // don't overwrite previous files that were uploaded and slug filename
//                     $file['name'] = Inflector::slug($File->name());
//                     $file['ext'] = $File->ext();
                     
//                     $file = $this->__uniqeSlugableFilename($bigFilePath);
                     
//                     debug($file);
//                     debug($File);
//                     die;

//                     $file_name = $file['name'] . '.' . $file['ext'];
                     $file_name = $this->__uniqeSlugableFilename($bigFilePath);

                     // copy file and save nodeattachment
                     if ($File->copy($this->uploads_path . DS . $file_name, true)) {
                            $data = array(
                                'owner' => $owner,
                                'owner_id' => $ownerId,
                                'slug' => $file_name,
                                'path' => '/' . $this->uploads_dir . '/' . $file_name,
                                'title' => $File->name(),
                                'status' => 1,
                                'mime_type' =>
                                $this->__getMime($this->uploads_path . DS . $file_name)
                            );
                            if ($this->Allattachment->save($data)) {
                                   //unlink($storage_path . DS . $this->params['named']['file']);
                                   $notice = array(
                                       'text' => __('File attached', true),
                                       'class' => 'success');
                            } else {
                                   $notice = array(
                                       'text' => __('Error during attachment saving', true),
                                       'class' => 'error');
                            }
                     }
              }
              
              // list files
//              debug($storage_path);
              $Folder = new Folder($storage_path);
              $content = $Folder->read();
              $this->set(compact('content', 'owner_id', 'owner', 'notice'));
       }

       /**
        * Delete storage file
        *
        * @return void
        */
       public function admin_deleteStorageFile() {

              $storage_path = Configure::read('Allattachment.storageUploadDir');

              if (!empty($this->params['named']['file'])) {
                     unlink($storage_path . DS . $this->params['named']['file']);
              }
              $this->redirect(array(
                  'plugin' => 'allattachment',
                  'controller' => 'allattachment',
                  'action' => 'addStorageFile',
                  'owner_id' => $this->params['named']['owner_id'],
                  'owner' => $this->params['named']['owner'],
              ));
       }

       /**
        * Unique filebname for upload
        *
        * @param string $fileName
        * @return array
        */
       private function __uniqeSlugableFilename($fileName = null) {
              $file = pathinfo($fileName);
              $fileName = $file['filename'];
              $lp = 1;
              while (file_exists($this->uploads_path . DS . $fileName . '.' . $file['extension'])) {
                     $fileName = $file['filename'] . '_' . $lp;
                     $lp++;
              }              
              $fileName .= '.'.$file['extension'];
              return $fileName;
       }

       /**
        * Edit attachment
        *
        * @param integer $id  Attachment id
        * @return void
        */
       public function admin_edit($id) {
           Configure::write('debug', 2);
              $this->set('title_for_layout', __('Edit attachment', true));
              if (!empty($this->data)) {
                     $this->Allattachment->save($this->data);
                     $this->redirect(array('action' => 'allattachmentIndex', 'owner' => $this->request->params['named']['owner'], 'owner_id' => $this->request->params['named']['owner_id']));
              }
              $this->data = $this->Allattachment->read(null, $id);
       }

       /**
        * Delete attachment
        *
        * @param integer $id Attachment id
        * @return void
        */
       public function admin_delete($id = null) {
              if (!$id) {
                     // wrond id, redirect
              }
              $attachment = $this->Allattachment->read(null, $id);
//              debug($attachment);
              if (!$this->Allattachment->delete($id)) {
                     // delete error redirect
              } else {
                  // entry deleted. Deleting file itself
                  if(is_file(WWW_ROOT.$attachment['Allattachment']['path'])){
                      unlink(WWW_ROOT.$attachment['Allattachment']['path']);
                  }
              }
              $this->redirect(array('action' => 'allattachmentIndex', 'owner' => $this->request->params['named']['owner'], 'owner_id' => $this->request->params['named']['owner_id']));
       }

       /**
        * Ajax Sort call
        *
        * @return array
        */
       public function admin_sort() {
              Configure::write('debug', 0);
              $this->disableCache();

              $ids = $this->request->data['allattachments'];
              foreach ($ids as $position => $id) {
                     $this->Allattachment->id = $id;
                     $position = $position + 3;
                     $this->Allattachment->saveField('priority', $position);
              }
              $this->render(false);
       }

       /**
        * Reset priority and set "created" from exif
        *
        * @return void
        */
       public function admin_resetPrioritySetCreated() {

              $res = $this->Allattachment->find('all');
              foreach ($res as $data) {
                     if ($data['Allattachment']['mime_type'] == 'image/jpeg' || $data['Allattachment']['mime_type'] == 'image/tiff') {
                            $file_path = $this->uploads_path . DS . $data['Allattachment']['slug'];
                            $exif = $this->Allattachment->getExif($file_path);
                            $data['Allattachment']['created'] = $exif['DateTime'];

                            $data['Allattachment']['priority'] = 1;

                            $this->Allattachment->create();
                            $this->Allattachment->save($data);
                     }
              }
       }

       /**
        * Get mimetype of file
        *
        * @param string $file Filename with full path
        * @return string
        */
       private function __getMime($file) {
              $mime_types = array(
                  'txt' => 'text/plain',
                  'htm' => 'text/html',
                  'html' => 'text/html',
                  'php' => 'text/html',
                  'css' => 'text/css',
                  'js' => 'application/javascript',
                  'json' => 'application/json',
                  'xml' => 'application/xml',
                  'swf' => 'application/x-shockwave-flash',
                  'flv' => 'video/x-flv',
                  // images
                  'png' => 'image/png',
                  'jpe' => 'image/jpeg',
                  'jpeg' => 'image/jpeg',
                  'jpg' => 'image/jpeg',
                  'gif' => 'image/gif',
                  'bmp' => 'image/bmp',
                  'ico' => 'image/vnd.microsoft.icon',
                  'tiff' => 'image/tiff',
                  'tif' => 'image/tiff',
                  'svg' => 'image/svg+xml',
                  'svgz' => 'image/svg+xml',
                  // archives
                  'zip' => 'application/zip',
                  'rar' => 'application/x-rar-compressed',
                  'exe' => 'application/x-msdownload',
                  'msi' => 'application/x-msdownload',
                  'cab' => 'application/vnd.ms-cab-compressed',
                  // audio/video
                  'mp3' => 'audio/mpeg',
                  'qt' => 'video/quicktime',
                  'mov' => 'video/quicktime',
                  'wmv' => 'video/x-ms-wmv',
                  'wma' => 'audio/x-ms-wma',
                  'avi' => 'video/x-msvideo',
                  'flv' => 'video/x-flv',
                  'wav' => 'audio/wav',
                  'mid' => 'audio/mid',
                  'mp4' => 'video/mp4',
                  // adobe
                  'pdf' => 'application/pdf',
                  'psd' => 'image/vnd.adobe.photoshop',
                  'ai' => 'application/postscript',
                  'eps' => 'application/postscript',
                  'ps' => 'application/postscript',
                  // ms office
                  'doc' => 'application/msword',
                  'rtf' => 'application/rtf',
                  'xls' => 'application/vnd.ms-excel',
                  'ppt' => 'application/vnd.ms-powerpoint',
                  // open office
                  'odt' => 'application/vnd.oasis.opendocument.text',
                  'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
              );

              $ext = strtolower(array_pop(explode('.', $file)));
              if (array_key_exists($ext, $mime_types)) {
                     return $mime_types[$ext];
              } elseif (function_exists('finfo_open')) {
                     $finfo = finfo_open(FILEINFO_MIME);
                     $mimetype = finfo_file($finfo, $file);
                     finfo_close($finfo);
                     return $mimetype;
              } else {
                     return 'application/octet-stream';
              }
       }

       /**
        * All attachments on site by node
        *
        * @return void
        */
       
       /*
       public function all() {

              $this->set('title_for_layout', __('All attachments', true));

              $this->paginate['Node']['order'] = 'Node.created DESC';
              $this->paginate['Node']['limit'] = Configure::read('Reading.nodes_per_page');
              $this->paginate['Node']['conditions'] = array(
                  'Node.status' => 1,
                  'OR' => array(
                      'Node.visibility_roles' => '',
                      'Node.visibility_roles LIKE' => '%"' . $this->Croogo->roleId . '"%',
                  ),
              );
              $this->paginate['Node']['contain'] = array(
                  'Meta',
                  'Taxonomy' => array(
                      'Term',
                      'Vocabulary',
                  ),
                  'User',
                  'Nodeattachment'
              );

              if (isset($this->params['named']['type'])) {
                     $type = $this->Node->Taxonomy->Vocabulary->Type->findByAlias($this->params['named']['type']);
                     if (!isset($type['Type']['id'])) {
                            $this->Session->setFlash(__('Invalid content type.', true), 'default', array('class' => 'error'));
                            $this->redirect('/');
                     }
                     if (isset($type['Params']['nodes_per_page'])) {
                            $this->paginate['Node']['limit'] = $type['Params']['nodes_per_page'];
                     }
                     $this->paginate['Node']['conditions']['Node.type'] = $type['Type']['alias'];
                     $this->set('title_for_layout', $type['Type']['title']);
                     $this->set(compact('type'));
              }

              if ($this->usePaginationCache) {
                     $cacheNamePrefix = 'nodes_promoted_' . $this->Croogo->roleId . '_' . Configure::read('Config.language');
                     if (isset($type)) {
                            $cacheNamePrefix .= '_' . $type['Type']['alias'];
                     }
                     $this->paginate['page'] = isset($this->params['named']['page']) ? $this->params['named']['page'] : 1;
                     $cacheName = $cacheNamePrefix . '_' . $this->paginate['page'] . '_' . $this->paginate['Node']['limit'];
                     $cacheNamePaging = $cacheNamePrefix . '_' . $this->paginate['page'] . '_' . $this->paginate['Node']['limit'] . '_paging';
                     $cacheConfig = 'nodes_promoted';
                     $nodes = Cache::read($cacheName, $cacheConfig);
                     if (!$nodes) {
                            $nodes = $this->paginate('Node');
                            Cache::write($cacheName, $nodes, $cacheConfig);
                            Cache::write($cacheNamePaging, $this->params['paging'], $cacheConfig);
                     } else {
                            $paging = Cache::read($cacheNamePaging, $cacheConfig);
                            $this->params['paging'] = $paging;
                            $this->helpers[] = 'Paginator';
                     }
              } else {
                     $nodes = $this->paginate('Node');
              }
              //debug($nodes);
              $this->set(compact('nodes'));
       }
        * 
        */

       /**
        * Filter all downloads by term and
        * - vocabulary_id
        * - nodeattachment category
        * - term level (todo)
        * - mime type (todo)
        * 
        *
        * @return void
        **/
       /*
       public function downloadsByTerms() {
              
              if (!isset($this->request->named['vocabulary'])) {
                     $this->Session->setFlash(__('Missing vocabulary id'));
                     $this->redirect(array('controller' => 'nodes', 'action' => 'promoted', 'plugin' => false));                     
              }
              $vocabulary = $this->Term->Vocabulary->findById($this->request->named['vocabulary']);

              $terms_tree = $this->Term->Taxonomy->getTree($vocabulary['Vocabulary']['alias'], array(
                     'key' => 'id',
                     'value' => 'title',
                     'cache' => true
              ));     

              $node_condition = array();
              $term_condition = array();
              foreach ($terms_tree as $term_id  => $term_title) {
                     $pos = strpos($term_title, '_');
                     if (($pos === false) || ($pos > 4)) {
                            $node_condition[] = array(
                                   'Node.terms LIKE' => "%\"".$term_id."\"%"
                            );
                            $term_condition[] = array( 
                                   'Term.id' => $term_id
                            );
                      }
              }
              $terms = $this->Term->find('all', array(
                     'conditions' => array(
                            'OR' => $term_condition,
                     ),
                     'order' => 'title ASC',
                     'recursive' => -1)
              );                
              $nodes = $this->Node->find('all', array(
                     'conditions' => array(
                            'OR' => $node_condition
                     ),
                     'order' => 'title ASC',
                     'cache' => array(
                            'name' => 'nodes_downloads_by_term_' . $vocabulary['Vocabulary']['id'],
                            'config' => 'nodes_term',
                     ))
              );
              $this->set(compact('terms', 'terms_tree', 'nodes'));      
       }
        * 
        */

}

?>