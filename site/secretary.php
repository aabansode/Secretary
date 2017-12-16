<?php
/**
 * @version     3.0.0
 * @package     com_secretary 
 * @author      Fjodor Schaefer - https://www.schefa.com
 */

defined('_JEXEC') or die;

// Load the required admin language files
$lang = JFactory::getLanguage();
$lang->load('joomla', JPATH_ADMINISTRATOR);
$lang->load('com_secretary', JPATH_ADMINISTRATOR);

$user	= JFactory::getUser();
$app	= JFactory::getApplication();
$view	= $app->input->getCmd('view','dashboard');
$task	= $app->input->getCmd('task');
$layout	= $app->input->getCmd('layout');
$parts  = explode(".",$task);

// Framework
require_once  JPATH_ADMINISTRATOR .'/components/com_secretary/application/Secretary.php';
JLoader::register('SecretaryFactory', JPATH_ADMINISTRATOR .'/components/com_secretary/helpers/factory.php');

$single = Secretary\Application::getSingularSection($view);
$canSee	= $user->authorise('core.show','com_secretary.'.$single);
if(in_array($view,array('dashboard')) || in_array($parts[0],array('ajax'))) 
    $canSee = $user->authorise('core.show','com_secretary.business');
if(($view === 'message' && $layout === 'form') OR $view === 'dashboard' OR $view === 'messages')
    $canSee = true;
    
/********************************************
 ************		Display       ************
 *********************************************/
    
if(true === boolval($canSee)) {
    include_once ( JPATH_ADMINISTRATOR .'/components/com_secretary/secretary.php');
} else {
    echo '<div class="alert alert-danger">'.JText::_('JERROR_ALERTNOAUTHOR').'</div>'; return false;
}
