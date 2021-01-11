<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

require 'Classes/PHPExcel.php';
/**
 * Redirect link list controller class.
 *
 * @since  1.6
 */
class AnchorControllerLinks extends JControllerAdmin
{
	/**
	 * Method to update a record.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected $default_view = 'links';

	public function search_keys(){
        $db = JFactory::getDbo();

        $input = trim(JRequest::getVar('keys'));
        $query = $db->getQuery(true)
            ->select($db->quoteName('article_alias'))
            ->select($db->quoteName('keyword'))
            ->select($db->quoteName('new_keyword'))
            ->select($db->quoteName('t2.id'))
            ->select($db->quoteName('t2.catid'))
            ->select($db->quoteName('t2.language'))
            ->from($db->quoteName('#__anchor','t1'))
            ->join('LEFT', $db->quoteName('#__content','t2') . ' ON t1.article_alias = t2.alias')
            ->where($db->quoteName('match_state') . ' = 1')
            ->where($db->quoteName('keyword') . ' = '."'".$input."'".' OR '.
            $db->quoteName('new_keyword') . ' = '."'".$input."'");

        $query_cnt =  $db->getQuery(true)
            ->select($db->quoteName('alias'))
            ->select($db->quoteName('id'))
            ->select($db->quoteName('catid'))
            ->select($db->quoteName('language'))
            ->select($db->quoteName('introtext'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('introtext') . ' like '."'%<a %>".$input."</a>%'");

       // echo($query->__toString());exit;
        $arr =  $alias = $arr_cnt = [];

        if($input!=null){
            $db->setQuery($query);
            $arr = $db->loadAssocList();
            $db->setQuery($query_cnt);
            $arr_cnt = $db->loadAssocList();
        }

        foreach ($arr as $v){
          $keyword = $v['new_keyword']==null?$v['keyword']:$v['new_keyword'];
            if($input==$keyword)
                $alias[]= $this->get_efs_url($v['id'],$v['catid'],$v['language']);
        }

        foreach ($arr_cnt as $v){
            $alias[] = $this->get_efs_url($v['id'],$v['catid'],$v['language']);
        }

        $alias = array_unique($alias);
        $view = $this->getView( 'search', 'html' );

        $res_count = count($alias);

        $flag = $res_count>0;

        $str_a = '';

        if($flag){
            $create_file = JPATH_COMPONENT . '/' . "tmp" . '/' .str_replace(' ','',$input).time().'.txt';
            $out_file =JURI::base() .substr($create_file,strpos($create_file,'/')+1);
            jimport('joomla.filesystem.file');
            $str_a = "<a href=$out_file target='_blank' >导出txt文件</a>";
        }

        $title = "锚文本关键字出现在以下文章（共".$res_count."篇）".$str_a."<br>";
        $res_str = '';
        foreach ($alias as $v){
            $res_str.= "<a href=".$v." target='_blank' rel='noopener noreferrer' >".$v."</a><br>";
            if($flag) JFile::append($create_file, $v."\n");
        }
        $data = [$res_str,$input,$title];

        $view->data = $data;
        $view->display();

    }

    public function get_efs_url($id,$catid,$lang){
        include_once JPATH_ROOT . '/components/com_content/helpers/route.php';
        $url = ContentHelperRoute::getArticleRoute( $id, $catid, $lang);
        $res = substr(juri::root(), 0, -1) . JRoute::link('site', $url);
        return $res;
    }

    public function export(){
        include_once JPATH_COMPONENT . '/models/links.php';
        $db = JFactory::getDbo();
        $lk = new AnchorModelLinks();
        $query = $lk->getListQuery();


        $db->setQuery($query);
        $arr = $db->loadAssocList();


        $arrHeader = array('anchor_id', 'keyword', 'new_keyword', 'target_url');
        $this->exportEx($arr,$arrHeader);

    }

    //导出excel
    public function exportEx($xlsData,$arrHeader)
    {

        $objExcel = new \PHPExcel();
        //设置文档属性
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        //设置内容
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter = explode(',', "A,B,C,D");
        //填充表头信息
        $lenth = count($arrHeader);
        for ($i = 0; $i < $lenth; $i++) {
            $objActSheet->setCellValue("$letter[$i]1", "$arrHeader[$i]");
        };
        //填充表格信息
        foreach ($xlsData as $k => $v) {
            $k+=2;

            //表格内容
            $objActSheet->setCellValue('A' . $k, $v['anchor_id']);
           // $objActSheet->setCellValue('B' . $k, $v['inner_url']);
            $objActSheet->setCellValue('B' . $k, $v['keyword']);
            $objActSheet->setCellValue('C' . $k, $v['new_keyword']);
            $objActSheet->setCellValue('D' . $k, $v['target_url']);
           // $objActSheet->setCellValue('F' . $k, $v['published']);
           // $objActSheet->setCellValue('G' . $k, $v['match_state']);
            //$objActSheet->setCellValue('H' . $k, $v['created_date']);
           // $objActSheet->setCellValue('I' . $k, $v['modified_date']);
          //  $objActSheet->setCellValue('E' . $k, $v['remark']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
        $width = array(20, 20, 15, 10, 10, 30, 10, 15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[5]);
        $objActSheet->getColumnDimension('B')->setWidth($width[1]);
        $objActSheet->getColumnDimension('C')->setWidth($width[0]);
        $objActSheet->getColumnDimension('D')->setWidth($width[5]);
        $objActSheet->getColumnDimension('E')->setWidth($width[5]);
        $outfile = "anchor_exdata" . time() . ".xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outfile . '"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");

        $objWriter->save('php://output');
        exit;

    }

    public function import() {
		$this->checkToken();

		$file = JFactory::getApplication()->input->files->get('importfile');
		jimport('joomla.filesystem.file');
        libxml_disable_entity_loader(false);
		if ($file["error"] > 0){
			echo "上传失败";
			return;
		}
		$allowedExts = ["xls", "xlsx", "xla"];
		$name = $file["name"];
		
		$temp = explode(".", $name);
		$extension = end($temp);
//var_dump(strpos($name,'anchor_exdata'));exit;

		;

		$file_url = $file["tmp_name"];
		
		if(!in_array($extension,$allowedExts)){
			echo "<h2>文件格式错误！请转存/或上传xlsx，xls，xla文件</h2>";
			return;
		}
		if($file["size"] > 2024000){
			echo '文件不超过2Mb！';
			return;
		}

        $is_ex = strpos($name,'anchor_exdata');

	$rmk = trim(JRequest::getVar('remark'));

    $create_file =     JPATH_COMPONENT . '/' . "tmp" . '/' .$name.time().'.txt';


    $out_file =JURI::base() .substr($create_file,strpos($create_file,'/')+1);

    $reader = PHPExcel_IOFactory::createReader('Excel5');
    $excel = PHPExcel_IOFactory::load($file_url);



    $SheetNamas = $excel->getSheetNames();
    $excel->setActiveSheetIndexByName($SheetNamas[0]);
    $curSheet = $excel->getActiveSheet();
    $rows = $curSheet->getHighestRow();

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);

	$match = "/^(https?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/";
	JLoader::register('AnchorHelper', JPATH_ADMINISTRATOR . '/components/com_anchor/helpers/anchor.php');
	$check_arr = AnchorHelper::get_anchor_arr();
       $ids = $columns = $values = $mis_matchs = [];

        $date = new Date(date("Y-m-d H:i:s"));
        $timezone = Factory::getUser()->getTimezone();
        $date->setTimezone($timezone);
        $mdate =  $date->format(Text::_('DATE_FORMAT_FILTER_DATETIME'));

      //  var_dump($mdate);exit;
	for($k = 2; $k <= $rows; $k++){
		$value_a = $curSheet->getCell('A'.$k)->getValue();
		$value_b = $curSheet->getCell('B'.$k)->getValue();
		$value_c = $curSheet->getCell('C'.$k)->getValue();
		$value_d = $curSheet->getCell('D'.$k)->getValue();


		if($is_ex===0){
            $columns = ['anchor_id','inner_url','keyword','new_keyword','target_url','published','match_state','created_date','modified_date','remark'];
            $ids[] = $value_a;
            $values[$value_a] = "'".$value_a."','inner_url','".$value_b."','".$value_c."','".$value_d."','1','0','".'created_date'."','".$mdate."','".$rmk."'";

        }else{
            $columns = ['article_alias','keyword','new_keyword','inner_url','target_url','published','remark'];
            $three_keys = ['inner_url'=>$value_a,'keyword'=>$value_b,'target_url'=>$value_d];
            if(!preg_match($match,$value_a) || !preg_match($match,$value_d) || in_array($three_keys,$check_arr) ){
                $mis_matchs[] = '[line]: '.$k.' [inner_url]: '.$value_a.' [keyword]: '.$value_b.' [new_keyword]: '.$value_c.' [target_url]: '.$value_d;
                continue;
            }

            $alisa = substr($value_a,strripos($value_a,'/')+1);
            $arr = [$alisa,$value_b,$value_c,$value_a,$value_d];
            $res = $this->changequote($arr);

            if(substr($res[0],-5)=='.html'){
                $res[0] = substr($res[0],0,-5);
            }

            $values[] = "'".$res[0]."','".$res[1]."','".$res[2]."','".$res[3]."','".$res[4]."','1',"."'".$rmk."'";
        }
	}
//echo '<pre>';

    if($values!=null && $is_ex!==0){
        $query->insert($db->quoteName('#__anchor'));
        $query->columns($columns);
        $query->values($values);
        $db->setQuery($query);
        $db->execute();
    }

    if($is_ex===0){
	    $ids = implode(',',$ids);
	    $query_a = $db->getQuery(true)
            ->select($db->quoteName('anchor_id'))
            ->select($db->quoteName('inner_url'))
            ->select($db->quoteName('created_date'))
            ->from($db->quoteName('#__anchor'))
            ->where($db->quoteName('anchor_id') .' in ('.$ids.')');
        $db->setQuery($query_a);
       // echo($query_a->__toString());exit;
        $a_arr = $db->loadAssocList();

        foreach ($a_arr as $v){
            $values[$v['anchor_id']] = str_replace('inner_url',$v['inner_url'],$values[$v['anchor_id']]);
            $values[$v['anchor_id']] = str_replace('created_date',$v['created_date'],$values[$v['anchor_id']]);
        }

        $query->delete($db->quoteName('#__anchor'))->where($db->quoteName('anchor_id').' in ('.$ids.')');
        $db->setQuery($query);
        $db->execute();
        $query_i = $db->getQuery(true)->insert($db->quoteName('#__anchor'))->columns($columns)->values($values);
        $db->setQuery($query_i);
        //echo($query_i->__toString());exit;
        $db->execute();
    }


	if($mis_matchs!=null){
		echo "<h3>以下记录未导入成功，原因如下。请检查后重新导入</h3>
            <ul>
                <li>链接格式不符合规范（存在引号、空格或特殊字符）</li>
                <li>原链接、关键词、目标链接，与现有记录同时重复</li>
                <a href=$out_file target='_blank' >>>导出txt文件</a>
            </ul>";
		foreach($mis_matchs as $v){
			echo "<div style='color:red'>$v</div>";
            JFile::append($create_file, $v."\n");
		}

	}else{
		echo "<h3>导入成功！<h3>";
        //return $this->setRedirect( 'index.php?option=com_anchor');
	}
    }

	
	public function changequote($arr=[]){
		if(is_null($arr))return null;
		$res = [];
		foreach($arr as $v){
			$res[] = str_replace("'","\'",$v);
		}
		return $res;
	}
	
	public function cancel($key = null) {
		$this->setRedirect( 'index.php?option=com_anchor');
	}
	public function activate()
	{
		
		// Check for request forgeries.
		$this->checkToken();
		$ids     = $this->input->get('cid', array(), 'array');
		$newUrl  = $this->input->getString('target_url');
		$remark = $this->input->getString('remark');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_REDIRECT1_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			$ids = ArrayHelper::toInteger($ids);

			// Remove the items.
			if (!$model->activate($ids, $newUrl, $remark))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				$this->setMessage(JText::plural('COM_REDIRECT1_N_LINKS_UPDATED', count($ids)));
			}
		}
		

		$this->setRedirect('index.php?option=com_anchor&view=links');
	}

	/**
	 * Method to duplicate URLs in records.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function duplicateUrls()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids     = $this->input->get('cid', array(), 'array');
		$newUrl  = $this->input->getString('target_url');
		$remark = $this->input->getString('remark');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_REDIRECT1_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			$ids = ArrayHelper::toInteger($ids);

			// Remove the items.
			if (!$model->duplicateUrls($ids, $newUrl, $remark))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				$this->setMessage(JText::plural('COM_REDIRECT1_N_LINKS_UPDATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix of the model.
	 * @param   array   $config  An array of settings.
	 *
	 * @return  JModel instance
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Link', $prefix = 'AnchorModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Executes the batch process to add URLs to the database
	 *
	 * @return  void
	 */
	public function batch()
	
	
	{
		// Check for request forgeries.
		$this->checkToken();

		$batch_urls_request = $this->input->post->get('batch_urls', array(), 'array');
		$batch_urls_lines   = array_map('trim', explode("\n", $batch_urls_request[0]));

		$batch_urls = array();

		foreach ($batch_urls_lines as $batch_urls_line)
		{
			if (!empty($batch_urls_line))
			{
				$params = JComponentHelper::getParams('com_redirect');
				$separator = $params->get('separator', '|');

				// Basic check to make sure the correct separator is being used
				if (!\Joomla\String\StringHelper::strpos($batch_urls_line, $separator))
				{
					$this->setMessage(JText::sprintf('COM_REDIRECT1_NO_SEPARATOR_FOUND', $separator), 'error');
					$this->setRedirect('index.php?option=com_redirect&view=links');

					return false;
				}

				$batch_urls[] = array_map('trim', explode($separator, $batch_urls_line));
			}
		}

		// Set default message on error - overwrite if successful
		$this->setMessage(JText::_('COM_REDIRECT_NO_ITEM_ADDED'), 'error');

		if (!empty($batch_urls))
		{
			$model = $this->getModel('Links');

			// Execute the batch process
			if ($model->batchProcess($batch_urls))
			{
				$this->setMessage(JText::plural('COM_REDIRECT1_N_LINKS_ADDED', count($batch_urls)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Clean out the unpublished links.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function purge()
	{
		// Check for request forgeries.
		$this->checkToken();

		$model = $this->getModel('Links');

		if ($model->purge())
		{
			$message = JText::_('COM_REDIRECT_CLEAR_SUCCESS');
		}
		else
		{
			$message = JText::_('COM_REDIRECT_CLEAR_FAIL');
		}

		$this->setRedirect('index.php?option=com_redirect&view=links', $message);
	}
}
