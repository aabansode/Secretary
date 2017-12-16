<?php
/**
 * @version     3.0.0
 * @package     com_secretary
 *
 * @author       Fjodor Schaefer (schefa.com)
 * @copyright    Copyright (C) 2015-2017 Fjodor Schaefer. All rights reserved.
 * @license      GNU General Public License version 2 or later.
 */

namespace Secretary\HTML;

require_once JPATH_ADMINISTRATOR .'/components/com_secretary/application/HTML.php';

use JText; 

// No direct access
defined('_JEXEC') or die;
 
class Documents
{ 
    /**
     * Method to get a summary of documents depending on their status
     * 
     * @param array $data
     * @param int $totalData
     * @return string HTML
     */
    public static function summary($data,$totalData) {
        
        $sum = array();
        $html = array(); 
        
        for($i = 0; $i < $totalData; $i++) {
            $lastCurrency = ($i > 0) ? $data[$i-1]->currency : '';
            $currentCurrency = $data[$i]->currency;
            $nextCurrency = (isset($data[$i+1]->currency)) ? $data[$i+1]->currency : 0;
            if(!isset($sum[$currentCurrency])) $sum[$currentCurrency] = array();
            $sum[$currentCurrency][] = $data[$i]->total;
            
            $html[] = JText::_($data[$i]->status_title) . ': ';
            $html[] = '<span class="brutto-'.$data[$i]->class.' pull-right">';
            $html[].= \Secretary\Utilities\Number::getNumberFormat($data[$i]->total,$data[$i]->currencySymbol);
            $html[].= '</span><br>';
            
            if( ($totalData-1) == $i || $currentCurrency !== $nextCurrency ) {
                
                if(count($sum[$currentCurrency]) > 1 && ($currentCurrency !==  $nextCurrency))
                {
                    $html[] = '<h4 class="text-right">';
                    $html[] = \Secretary\Utilities\Number::getNumberFormat(array_sum($sum[$currentCurrency]),$data[$i-1]->currencySymbol);
                    $html[] = '</h4>';
                }
                
                if(($totalData-1) !== $i)
                    $html[] = '<div class="sidebar-split"></div>';
            }
        }
        
        return implode('',$html);
    }
}
