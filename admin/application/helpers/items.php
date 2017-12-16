<?php
/**
 * @version     3.0.0
 * @package     com_secretary
 *
 * @author       Fjodor Schaefer (schefa.com)
 * @copyright    Copyright (C) 2015-2017 Fjodor Schaefer. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

namespace Secretary\Helpers;

use JEditor;
use JFactory;
use JHtml;
use JText;
use stdClass;

// No direct access
defined('_JEXEC') or die;

class Items
{
    protected static $_reqFields = array();
    
    public static $allowedHTML = "<p><strong><b><i><u><a><s><big><small><ul><li><ol><blockquote><h1><h2><h3><div><img><h4><h5><h6><sub><sup><mark><br><table><thead><tbody><tr><td><hr><pre>";
    
	/* 
	**  D A T A F I E L D S
	*/
	
	public static function getField( $id = 1, $extension = 'documents', $default = '', $counter = "##counter##" )
	{
	    $db = \Secretary\Database::getDBO();
		$query = $db->getQuery(true);
 
		$additional = "";
		if($extension == 'subjects') $additional = " OR ". $db->qn('extension')." = ".$db->quote("newsletters");
		
		$query->select('*')
			->from($db->qn('#__secretary_fields'))
			->where($db->qn('id').'='.$db->escape($id))
			->where('( ('.$db->qn('extension').'='. $db->quote($extension) .' OR extension = '.$db->quote("folders").' '. $additional . ') OR '.$db->qn('extension').'='. $db->quote("system").')')
			->order('id');
		$db->setQuery($query);
		$field = $db->loadObject();
		
		if(!empty($field)) {
			$standard = (strlen($default)>0) ? $default : $field->standard;
			
			// Do Data Field
			$return = new stdClass;
			$return->id = $field->id;
			$return->hard = $field->hard;
			$return->type = $field->type;
			
			if($field->type == 'accounts_tax') {
			    $return->title = JText::_('COM_SECRETARY_FIELD_ACCOUNT_TAX_TITLE');
			} else {
    			$return->title = \Secretary\Utilities::cleaner(JText::_($field->title), true);
			}
			
			$return->description = JText::_(trim($field->description));
			$return->box = self::getFieldTypes($field, $standard, $counter);
			
			if(!is_object($return)) die;
			
			return $return;
		}
		return false;
	}
	
	public static function getFieldTypes( $object, $standard = NULL, $counter = "##counter##")
	{
		$name = "jform[fields][".$counter."][values]";
		$placeholder = '';
		$html = '';
		$options = array();
		
		$types = array('accounts_tax','color','date','list','number','text','textarea','url','sql','search','html');
		if(!in_array($object->type, $types)) return $html;
		switch($object->type) {
		    
			case 'list':
				if( $fieldvalues = json_decode($object->values, true) ) {
				foreach($fieldvalues AS $key => $val) $options[] = JHtml::_( 'select.option', $key,  JText::_($val));
				}
				$html = JHtml::_('select.genericlist',$options, $name,'','value','text',\Secretary\Utilities::cleaner($standard));
				break;
			case 'sql':
				$canManage = JFactory::getUser()->authorise('core.admin');
				if($canManage == 1) { 
					$db = JFactory::getDbo();
					$db->setQuery( $object->values );
					$items = $db->loadObjectlist();
					
					if(empty($items)) {
						$options = array( 0 => JText::_('COM_SECRETARY_NONE') );
					} else 
						foreach($items as $item) $options[] = JHtml::_('select.option', $item->id, JText::_($item->title) );
					
					$html = JHTML::_('select.genericlist',$options, $name,'','value','text',\Secretary\Utilities::cleaner($standard));
				}
				break;	
			case 'accounts_tax': case 'search':
			    $table = ($object->type == 'accounts_tax') ? 'accounts_system' : $object->values;
			    if($standard > 0) {
			        $searchValue = JText::_('COM_SECRETARY_'.$table);
			        if($object->values == 'accounts_system' || $object->type == 'accounts_tax') {
    			        $acc = \Secretary\Database::getQuery($table, $standard,'id','nr,title');
    			        $title = $acc->nr.' '.$acc->title;
			        } else {
			            $title = \Secretary\Database::getQuery($table, $standard,'id','title','loadResult');
			        }
    				$html = '<input class="search-'.$table.'" type="text" value="'.$title.'" placeholder="'.JText::sprintf('COM_SECRETARY_SEARCH_THIS',$searchValue).'" />';
			    } else {
    				$html = '<input class="search-'.$table.'" type="text" value="" />';
			    }
			    $html .= '<input type="hidden" value="'.$standard.'" name="'.$name.'" />';
				break;
			case 'textarea': case 'html' :
				$html = '<textarea class="secretary-fields-textarea" name="'.$name.'">'.$standard.'</textarea>';
				break;
			case 'color':
				$html = '<input name="'.$name.'" type="'.$object->type.'" value="#'.$standard.'" />';
				break;
			case 'editor':
        		jimport( 'joomla.html.editor' );
        		$editor = JEditor::getInstance('tinymce');
        		$html = $editor->display($name, $standard, '550', '400', '60', '20', true);
        		break;
			default:
				if($object->type === 'text' && !is_numeric($counter)) $placeholder = ' placeholder="'. JText::_('COM_SECRETARY_FIELD_VALUE') .'"';
				$html = '<input name="'.$name.'" type="'.$object->type.'" value="'.\Secretary\Utilities::cleaner($standard).'" '. $placeholder .' />';
				break;	
		}

		return $html ;
		
	}
	
	public static function getFieldRaw( $id = NULL, $default = NULL , $return = false)
	{
		$obj = \Secretary\Database::getQuery('fields',$id); 
		if($return) {
		    return array( JText::_($obj->title) , $default );
		} else {
    		return JText::_($obj->title) .' '. $default;
		}
	}
	
     
	/** object list of standard fields **/
	public static function getFields($extension, $nots = array())
	{
	    $db = \Secretary\Database::getDBO();
		$query = $db->getQuery(true);
		
		$additional = "";
		if($extension == 'subjects') $additional = " OR ". $db->qn('extension')." = ".$db->quote("newsletters");
		
		$query->select('*')
				->from($db->qn('#__secretary_fields'))
				->where(' ( ('. $db->qn('extension').' = '. $db->quote($extension) . ' OR extension = '.$db->quote("folders").' '. $additional . ') OR '.$db->qn('extension').'='. $db->quote("system") .')');
		
				
		if(!empty($nots)) {
			foreach($nots as $not){
				$query->where($db->qn('hard').' != '. $db->quote($not) );
			}
		}
		$query->order('id');
		$db->setQuery($query);
		
		$result = $db->loadObjectList();
		return $result;
	}
	 
	public static function getRequiredFields($extension)
	{
		if(empty($_reqFields[$extension])) {
				
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id,hard,title,standard')
					->from($db->qn('#__secretary_fields'))
					->where($db->qn('extension').'='. $db->quote($extension)  . ' OR '.$db->qn('extension').'='. $db->quote("system"))
					->where($db->qn('required').'=1')
					->order('id');
			$db->setQuery($query);
			self::$_reqFields[$extension] = $db->loadObjectList();
		}
		
		return self::$_reqFields[$extension];
		
	}
 
	public static function saveFields( $datas = FALSE )
	{
	    $fields = array();
	    if(!$datas)
            return FALSE;
	        
	    foreach($datas as $key => $field) {
	        
	        if(is_numeric($key)) {
	            
	            $datafieldtype = \Secretary\Database::getQuery('fields',intval($field['id']),'id','type','loadResult');
	            
    	        if($datafieldtype === 'html') {
    	            $values = strip_tags($field['values'],self::$allowedHTML);
    	        } else {
    	            $values = strip_tags($field['values']);
    	        }
	            $fields[] = array( (int) $field['id'] , htmlentities( $field['title'] ), htmlentities($values), htmlentities( $field['hard'] ) );
	        } else {
	            $fields[$key] = $field;
	        }
	    }

	    return json_encode($fields, JSON_NUMERIC_CHECK);
	}
	
	
	public static function rebuildFieldsForDocument( $datas = array())
	{
		$fields = array();
		if(empty($datas))
		    return array();
		
		foreach($datas as $key => $field) {
			if(is_numeric($key) && isset($field[0])) { 
				$fields[] = array( (int) $field[0] ,htmlentities(  stripslashes($field[1] )), htmlentities( stripslashes($field[2] ) ), htmlentities( stripslashes($field[3] )) );
			} elseif( isset($field) ) {
				$fields[$key] = $field;
			}
		}
		return json_encode($fields,true);
	}
	
	public static function makeFieldsReadyForList( &$data )
	{
		if(empty($data))
		    return;
		
		// Data Fields
		if($fields = json_decode($data,true)) {
			// Textarea line breaks bug with javascript
			foreach($fields AS $key => $field)
			{
				if(isset($fields[$key][1]) && isset($fields[$key][2]) && isset($fields[$key][3]))
				{	
					if($fields[$key][3] === 'textarea') {
						$fields[$key][2] = preg_replace("/\n/","BREAK",$field[2]);
					}
					$fields[$key][1] = JText::_(\Secretary\Utilities::cleaner($fields[$key][1] ,true));
					$fields[$key][2] = \Secretary\Utilities::cleaner($fields[$key][2] ,true);
					$fields[$key][3] = \Secretary\Utilities::cleaner($fields[$key][3] ,true);
				}
			}
			$result['count'] = count($fields);
			$result['fields'] = json_encode($fields, true);
		} else {
			$result = array('count'=>0,'fields'=>'');
		}
		return $result;
	}
 
}
