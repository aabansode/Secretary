<?php
/**
 * @version     3.0.0
 * @package     com_secretary
 *
 * @author       Fjodor Schaefer (schefa.com)
 * @copyright    Copyright (C) 2015-2017 Fjodor Schaefer. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */
 
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin'); 

class SecretaryControllerItems extends Secretary\Controller\Admin
{
    
    protected $app;
    protected $catid;
    protected $view;
    protected $extension;
    protected $redirect_url;
    
    public function __construct() {
        $this->app		  = \Secretary\Joomla::getApplication();
        $this->catid      = $this->app->input->getInt('catid');
        $this->view       = $this->app->input->getCmd('view');
        $this->extension  = $this->app->input->getCmd('extension');
        $this->redirect_url = 'index.php?option=com_secretary&amp;view='.$this->view.'&amp;extension='. $this->extension;
        parent::__construct();
    }
    
	public function getModel($name = 'Item', $prefix = 'SecretaryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	public function postDeleteUrl()
	{
	    $this->setRedirect(JRoute::_($this->redirect_url, false));
	}
	
	public function saveOrder()
	{ 
		$user  = JFactory::getUser();
		$order = $this->app->input->get('order', array(), 'array');
	    $msg   = JText::_('COM_SECRETARY_ORDERING_SAVED_FAILED');
	    if($user->authorise('core.admin', 'com_secretary') && !empty($order)) {
	        $db = JFactory::getDbo();
	        $oldOrders = array();
	        $oldOrdersTasks = array();
	        $start = 1;
	        foreach($order as $key => $values) { 
    	        foreach($values as $id) {
                    $query = "UPDATE `#__secretary_status` SET `ordering` = ".$start." WHERE extension = ". $db->quote($key) ." AND id =". (int) $id;
                    $db->setQuery($query);
                    $db->execute();
                    $start++;
    	        }
	        }
	        $msg = JText::_('COM_SECRETARY_ORDERING_SAVED');
	    }
	     
	    $this->setMessage( $msg);
	    $this->setRedirect(JRoute::_('index.php?option=com_secretary&amp;view=items&amp;extension=status', false));
	}
	
	public function deleteFiles() {

	    JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
	    
	    $files = $this->app->input->get('cid', array(), 'array');
		$user  = JFactory::getUser();

		if($user->authorise('core.delete', 'com_secretary')) {
		    $x = 0;
		    foreach($files as $file) {
		        if(file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/uploads/'.$file)) {
                    unlink(JPATH_COMPONENT_ADMINISTRATOR.'/uploads/'.$file);
                    $x++;
		        }
		    }
            $this->setMessage(JText::plural('COM_SECRETARY_N_ITEMS_DELETED',$x));
		}
		
	    $this->setRedirect(JRoute::_('index.php?option=com_secretary&amp;view=items&amp;extension=uploads', false));
	}

	private function deleteFile($pk = null)
	{
	
		// Uploads löschen
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder'); 
	
		if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		$upload = Secretary\Database::getQuery('uploads', $pk );
	
		$path = JPATH_COMPONENT_ADMINISTRATOR . DS .'uploads' .DS. $upload->business . DS . $upload->folder . DS . $upload->title;
		if( JFile::delete($path) ) {
			if($upload->itemID > 0) $this->_updateItemDocument($upload->itemID, $upload->extension, $pk);
			$this->app->enqueueMessage(JText::sprintf('COM_SECRETARY_UPLOAD_DELETED',$upload->title), 'notice');
		} else {
		    $this->app->enqueueMessage(JText::sprintf('COM_SECRETARY_UPLOAD_DELETED_NOT',$upload->title), 'error');
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__secretary_uploads'))
			->where($db->quoteName('id') . ' = ' . $db->escape($pk));
	
		$db->setQuery($query);
		$db->execute();
		 
	}

	private function _updateItemDocument($itemID, $extension, $uploadID)
	{
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$fields		= array($db->qn('upload') . " = ''");
		$conditions	= array($db->qn('id') . ' = '. $db->escape($itemID), $db->qn('upload') . ' = '. $db->escape($uploadID));
		$query->update($db->qn('#__secretary_'. $extension))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
	}
}