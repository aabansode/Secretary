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
?>
<div class="container-fluid">

<div style="width:200px"> 
<?php echo Secretary\HTML::_('documents.summary', $this->item->documents_summary['data'], $this->item->documents_summary['totat_amount']); ?>
</div>
<hr />

<table class="table table-hover">
    <thead>
        <tr>
            <td></td>
            <td><?php echo JText::_('COM_SECRETARY_CREATED'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_DOCUMENT'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_NR'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_NETTO'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_TAX'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_TOTAL'); ?></td>
            <td><?php echo JText::_('COM_SECRETARY_STATUS'); ?></td>
        </tr>
    </thead>
	<tbody>
        <?php
        foreach($this->item->documents AS $i => $item) { 
		
		if( ($taxTotal = json_decode($item->taxtotal, true)) && is_array($taxTotal)) {
			$taxTotal = array_sum($taxTotal);
		} else {
			$taxTotal = floatval($item->taxtotal);
		}
		?>
        <tr>
            <td>
            <?php if($item->template > 0) { ?>
                <a class="hasTooltip" data-original-title="<?php echo JText::_('COM_SECRETARY_SHOW'); ?>" title="<?php echo JText::_('COM_SECRETARY_SHOW'); ?>" href="<?php echo Secretary\Route::create('document', array('id' => $item->id)); ?>"><i class="fa fa-newspaper-o"></i></a>
                 
				<?php if(COM_SECRETARY_PDF) { ?>
				<?php $href = Secretary\Route::create('document', array('id' => $item->id, 'format' => 'pdf')); ?>
				<a class="hasTooltip printpdf modal" href="<?php echo $href; ?>" data-original-title="<?php echo JText::_('COM_SECRETARY_PDF_PREVIEW') ; ?>" rel="{size: {x: 900, y: 500}, handler:'iframe'}"><img src="<?php echo JURI::root(); ?>/media/secretary/images/pdf-20.png" /></a>
				<?php } ?>
				            
			<?php } ?>
            </td>
            <td><a href="<?php echo Secretary\Route::create('document', array('id'=>(int) $item->id)); ?>"><?php echo $item->created; ?></a></td>
            <td><?php echo JText::_($item->category_title); ?></td>
            <td><?php echo $item->nr; ?></td>
            <td><?php echo Secretary\Utilities\Number::getNumberFormat($item->subtotal) .' '.$item->currencySymbol; ?></td>
            <td><?php echo Secretary\Utilities\Number::getNumberFormat($taxTotal).' '.$item->currencySymbol; ?></td>
            <td><strong><?php echo Secretary\Utilities\Number::getNumberFormat($item->total).' '.$item->currencySymbol; ?></strong></td>
            <td>
            <?php
            $state = array('title' => $item->status_title,'class' => $item->class,'description' => $item->tooltip,'icon' => $item->icon );
            echo Secretary\HTML::_('status.state', $item->state, $i, 'documents.', false, $state );
            ?>
            </td>
        </tr>
        <?php } ?>
    </tbody> 
	</table>
</div>
