<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2019 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

?>
<div style="margin: 0 auto; margin-top:20px; width:20%; text-align: center;">
<h1>
    <?php echo JText::_('导入Excel文件'); ?>
</h1>


<form action="<?php echo JRoute::_('index.php?option=com_anchor&view=links&tmpl=component'); ?>" method="post" name="adminFormImport" id="adminFormImport" enctype="multipart/form-data">

	<input type="hidden" name="option" value="com_anchor" />
	<input type="hidden" name="task" value="links.import" />

	<fieldset>  

		<div class="control-group">
            <div style="text-align: left;">
			<div class="controls" style="padding-top:5px">
              文件：  <input type="file" name="importfile" id="importfile">

            </div>
            <div class="controls" style="padding-top:5px">

               备注： <input type="text"  name="remark" id="remark">
            </div>


                <div style="padding-top:5px">提示：支持xlsx，xls，xla格式文件</div>
                <div class="controls" style="padding-top:10px">

                    <button type="submit" name="task" id="import" value="links.import">导入Excel</button>
                </div>
            </div>
		</div>

	</fieldset>  

	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
<script>
   var btn = document.getElementById("import");
   btn.onclick = function () {
       var file = adminFormImport.importfile.value;
       if(file=='' || file==null || typeof (file)=='undefined'){
           alert('请选择文件');
           return false
       }

       var value = adminFormImport.remark.value;
       if(value=='' || value==null || typeof (value)=='undefined'){
           if(confirm('未填写备注信息，是否继续？')){
               return true
           }else{
               return false
           }
       }


   }



</script>

