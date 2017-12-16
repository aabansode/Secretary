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

jimport('joomla.application.component.modeladmin');

class SecretaryModelBusiness extends JModelAdmin
{
    private static $_item;
    protected $app;
    
    /**
     * Class constructor
     * 
     * @param array $config
     */
	public function __construct($config = array())
	{
	    $this->app = JFactory::getApplication();
		parent::__construct();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\AdminModel::canDelete()
	 */
	protected function canDelete($record)
	{
		return \Secretary\Helpers\Access::canDelete($record,'business');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\AdminModel::populateState()
	 */
	protected function populateState()
	{
	    $pk = $this->app->input->getInt('id');
		$this->setState($this->getName() . '.id', $pk);
		
		$params = Secretary\Application::parameters();
		$this->setState('params', $params);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\BaseDatabaseModel::getTable()
	 */
	public function getTable($type = 'Business', $prefix = 'SecretaryTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\FormModel::getForm()
	 */
	public function getForm($data = array(), $loadData = true)
	{ 
		$form = $this->loadForm('com_secretary.business', 'business', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;
		return $form;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\FormModel::loadFormData()
	 */
	protected function loadFormData()
	{
	    $item = $this->app->getUserState('com_secretary.edit.business.data', array());
		if (empty($item)) { $item = $this->getItem(); }
		return $item;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\AdminModel::getItem()
	 */
	public function getItem($pk = null)
	{
		if(empty(self::$_item[$pk]) && ($item = parent::getItem($pk)))
		{
			$item->title         = Secretary\Utilities::cleaner($item->title,true);
			$item->slogan        = Secretary\Utilities::cleaner($item->slogan,true);
			$item->address       = Secretary\Utilities::cleaner($item->address,true);
			$item->defaultNote   = Secretary\Utilities::cleaner($item->defaultNote,true);
		    $item->createdEntry  = (empty($item->createdEntry)) ? time() : $item->createdEntry;
			
			$selectedCategories  = array('documents'=>array(),'subjects'=>array(),'products'=>array(),'messages'=>array());
			$item->selectedFolders	= json_decode($item->selectedFolders);
			$item->selectedFolders = array_replace( $selectedCategories, (array) $item->selectedFolders );

			$item->guv1				= json_decode($item->guv1);
			$item->guv2				= json_decode($item->guv2);
			
			$item->owner = (!empty($item->owner)) ? $item->owner : JFactory::getUser()->id;
		
			self::$_item[$pk] = $item;
		}
		
		return self::$_item[$pk];
	}

	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\AdminModel::save()
	 */
	public function save($data)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Initialise variables; 
		$user	= \Secretary\Joomla::getUser();
		$table	= $this->getTable();
		$key	= $table->getKeyName();
		$pk		= (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName().'.id');
		
		// Access
		if(!(\Secretary\Helpers\Access::checkAdmin())) {
			if ( !$user->authorise('core.create', 'com_secretary.business') || ($pk > 0 && !$user->authorise('core.edit.own', 'com_secretary.business.'.$pk) ) )
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				return false;
			}
		}
		
		// Allow an exception to be thrown.
		try
		{
			// Load existing record.
			if ($pk > 0) { $table->load($pk); }

			// Prepare data
			$table->prepareStore($data);
			
			// Bind
			if (!$table->bind($data)) { $this->setError($table->getError()); return false; }
			
			// Store
			if (!$table->store()) { $this->setError($table->getError()); return false; }
			
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		} 

		$newID = (int) $table->id;
		$this->setHome($newID);
		
		// Update Upload Document 
		if($user->authorise('core.upload', 'com_secretary') )
		{
		    $uploadTitle = (isset($data['upload_title'])) ? $data['upload_title'] : '';
		    \Secretary\Helpers\Uploads::upload( 'logo', 'businesses', $uploadTitle, $newID );
		}
		
		// Activity
		$activityAction = ($pk > 0) ? 'edited' : 'created';
		\Secretary\Helpers\Activity::set('businesses', $activityAction, 0, $newID );
		
		if (isset($table->id)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}

		$this->cleanCache();
		return true;
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Joomla\CMS\MVC\Model\BaseDatabaseModel::cleanCache()
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_secretary');
	}
	
	/**
	 * Set standard company
	 * 
	 * @param number $id 
	 * @return boolean
	 */
	public function setHome($id = 0)
	{
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access check 
		if (!$user->authorise('core.edit', 'com_secretary.business')) {
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			return false;
		}

		$business = $this->getTable();
		if (!$business->load((int) $id)) {
		    throw new Exception(JText::_('COM_SECRETARY_NOT_FOUND'));
		    return false;
		}
		
		// Reset the home fields for all
		$db->setQuery('UPDATE #__secretary_businesses SET home = \'0\'');
		$db->execute();

		// Set the new home business.
		$db->setQuery('UPDATE #__secretary_businesses SET home = \'1\' WHERE id = ' . (int) $id );
		$db->execute();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}
	
}