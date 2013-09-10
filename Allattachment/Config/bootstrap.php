<?php
        $tmp_conf = Configure::read('Allattachment');
        $conf = array(
            'thumbDir' => APP . 'plugins' . DS . 'allattachment' . DS .
                'webroot' . DS . 'img' . DS . 'tn',
            'iconDir' => APP . 'plugins' . DS . 'allattachment' . DS .
                'webroot' . DS . 'img',
            'flvDir' => APP . 'plugins' . DS . 'allattachment' . DS .
                'webroot' . DS .'flv',
            'thumbExt' => 'png'
        );
        Configure::write('Allattachment', Set::merge($tmp_conf, $conf));
        Configure::write('Allattachment.thumbnailExt', 'png');

        $attachTo = Configure::read('Allattachment.attachedTo');
        $attachTo = explode(',', $attachTo);
        foreach($attachTo as $to){
            $to = trim($to);
            Croogo::hookBehavior($to, 'Allattachment.Allattachment');
            Croogo::hookAdminTab(Inflector::pluralize($to).'/admin_edit', 'Allattachment', 'Allattachment.admin_tab');
//            Croogo::hookHelper('*', 'Attachment.Attachment');
            Croogo::hookHelper(Inflector::pluralize($to), 'Allattachment.Allattachment');
        }
        
        CroogoNav::add('extensions.children.allattachment', array(
            'title' => 'Allattachment',
            'url' => '#',
            'children' => array(
                'settings' => array(
                    'title' => __('Configure'),
                    'url' => array('plugin' => 'settings', 'controller' => 'settings', 'action' => 'prefix', 'Allattachment')
                )
            )
        ));
        
        Croogo::hookAdminMenu('Allattachment');
?>