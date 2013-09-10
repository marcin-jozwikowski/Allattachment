<?php
/**
 * Plugin activation
 *
 * Description
 *
 * @package  Croogo
 * @author Marcin Jóźwikowski <marcin@jozwikowski.pl>
 * @nodeattachment-author Juraj Jancuska <jjancuska@gmail.com>
 */
class AllattachmentActivation {

        /**
         * Schema directory
         *
         * @var string
         */
        private $SchemaDir;

        /**
         * DB connection
         *
         * @var object
         */
        private $db;

        /**
         * Plugin name
         *
         * @var string
         */
        public $pluginName = 'Allattachment';

        /**
         * Constructor
         *
         * @return vodi
         */
         public function  __construct() {

                 $this->SchemaDir = APP.'Plugin'.DS.$this->pluginName.DS.'Config'.DS.'Schema';
                 $this->db =& ConnectionManager::getDataSource('default');

        }

        /**
         * Before onActivation
         *
         * @param object $controller
         * @return boolean
         */
        public function beforeActivation(&$controller) {

                App::uses('CakeSchema', 'Model');
                $CakeSchema = new CakeSchema();
                
                $all_tables = $this->db->listSources();

                // list schema files from config/schema dir
                if (!$cake_schema_files = $this->_listSchemas($this->SchemaDir))
                        return false;

                // create table for each schema
                foreach ($cake_schema_files as $schema_file) {
                        $schema_name = substr($schema_file, 0, -4);
                        $schema_class_name = Inflector::camelize($schema_name).'Schema';
                        $table_name = $schema_name;

                         include_once($this->SchemaDir.DS.$schema_file);
                         $ActiveSchema = new $schema_class_name;

                        if (!in_array($table_name, $all_tables)) {
                                // create table
                                 if(!$this->db->execute($this->db->createSchema($ActiveSchema, $table_name))) {
                                         return false;
                                 }
                        } else {
                                // add columns to existing table if neccessary
                                /*$OldSchema = new CakeSchema(array('plugin' => $this->pluginName));
                                $old_schema = $OldSchema->read();

                                $alter = $ActiveSchema->compare($old_schema);
                                unset($alter[$table_name]['drop'], $alter[$table_name]['change']);

                                if (!$this->db->execute($this->db->alterSchema($alter))) {
                                        return false;
                                }*/
                                
                        }
                }


                return true;

        }

        /**
         * Activation of plugin,
         * called only if beforeActivation return true
         *
         * @param object $controller
         * @return void
         */
        public function onActivation(&$controller) {

                $controller->Setting->write('Allattachment.maxFileSize', '10', array(
                    'editable' => 1, 'description' => __('Max. size of uploaded file (MB)', true))
                );
                $controller->Setting->write('Allattachment.attachedTo', 'Node,Contact', array(
                    'editable' => 1, 'description' => __('Models to attach to. Names in SINGULAR', true))
                );
                $controller->Setting->write('Allattachment.allowedFileTypes', 'jpg,gif,png', array(
                    'editable' => 1, 'description' => __('Coma separated list of allowes extensions (empty = all files)', true))
                );
                $controller->Setting->write('Allattachment.storageUploadDir', WWW_ROOT.'uploads'.DS.'big', array(
                    'editable' => 1, 'description' => __('Full path to directory for big files. You can use it for FTP files uploading', true))
                );
                $controller->Setting->write('Allattachment.ffmpegDir', 'n/a', array(
                    'editable' => 1, 'description' => __('Directory with ffmpeg, type n/a if not installed', true))
                );
                $controller->Setting->write('Allattachment.types', __('Gallery,Cover', true), array(
                    'editable' => 1,
                    'description' => __('Coma separated list of attachment types', true))
                );
                $controller->Setting->write('Allattachment.ffmpegExec', 0, array(
                    'editable' => 1,
                    'description' => __('Run ffmpeg via exec(), more info about permission at http://en.php.net/manual/en/function.exec.php ', true),
                    'input_type' => 'checkbox')
                );

                $controller->Croogo->addAco('Allattachment.Allattachment');
                $controller->Croogo->addAco('Allattachment.Allattachment/add', array('registered', 'public')); 
//                $controller->Croogo->addAco('Allattachment/downloadsByTerms', array('registered', 'public')); 
        }

        /**
         * Before onDeactivation
         *
         * @param object $controller
         * @return boolean
         */
        public function beforeDeactivation(&$controller) {

                // list schema files from config/schema dir
                if (!$cake_schema_files = $this->_listSchemas($this->SchemaDir))
                        return false;

                // delete tables for each schema
                foreach ($cake_schema_files as $schema_file) {
                        $schema_name = substr($schema_file, 0, -4);
                        $table_name = $schema_name;
                        /*if(!$this->db->execute('DROP TABLE '.$table_name)) {
                                return false;
                        }*/
                }
                return true;

        }

        /**
         * Deactivation of plugin,
         * called only if beforeActivation return true
         *
         * @param object $controller
         * @return void
         */
        public function onDeactivation(&$controller) {

                $controller->Setting->deleteKey('Allattachment');

        }

        /**
         * List schemas
         *
         * @return array
         */
        private function _listSchemas($dir = false) {

                if (!$dir) return false;

                $cake_schema_files = array();
                if ($h = opendir($dir)) {
                        while (false !== ($file = readdir($h))) {
                                if (($file != ".") && ($file != "..") && ($file != ".svn")) {
                                        $cake_schema_files[] = $file;
                                }
                        }
                } else {
                        return false;
                }

                return $cake_schema_files;

        }
}
?>