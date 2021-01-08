<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Contact Plugin
 *
 * @since  3.2
 */
class PlgContentAnchorplg extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.3
	 */
	protected $db;

	/**
	 * Plugin that retrieves contact information for contact
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */


	public function onContentPrepare($context, &$row, $params, $page = 0)
	{

		$allowed_contexts = array('com_content.article');

		if (!in_array($context, $allowed_contexts))
		{
			return true;
		}

//  $file_path = JPATH_PLUGINS.'\content\anchorplg\tmp\\'.$row->alias.'.txt';
// $has_unpublish = $this->check_init_state($row->alias);

        $url = $this->get_efs_url($row->id,$row->catid,$row->language);

		$query = $this->db->getQuery(true)
        ->select($this->db->quoteName('keyword'))
		->select($this->db->quoteName('new_keyword'))
		->select($this->db->quoteName('target_url'))
        ->select($this->db->quoteName('match_state'))
		->from($this->db->quoteName('#__anchor'))
		->where($this->db->quoteName('inner_url') . ' = ' . "'".$url."'")
        ->where($this->db->quoteName('published') . ' = 1');
		$this->db->setQuery($query);


//echo($query->__toString());exit;
		$arr = $this->db->loadAssocList();

        foreach($arr as $v){
            $content_text = $row->text;

			$keyword = trim($v['keyword']);$tags = "</a>";$is_new = 0;$success = 2;

			if($v['new_keyword']!=null){

                $content_text = substr_replace($content_text,$v['new_keyword'],stripos($content_text,$keyword),strlen($keyword));

				$keyword = trim($v['new_keyword']);
                $is_new = 1;
			}



			$lst_index = stripos($content_text,$keyword);

			$matching = false;


			$out_inside = $this->filter_inner_tags($content_text,$lst_index);
            $out_fqa = $this->filter_other_tags($content_text,$lst_index,'<div class="g-faq"');
            $out_recommend = $this->filter_other_tags($content_text,$lst_index,'<div class="article-recommend"');


//var_dump($out_recommend)     ;exit;
            if($lst_index && $out_inside  && $out_fqa && $out_recommend){
                //关键词被包含时跳过，并尝试继续往下匹配
                while ($this->is_included_tags($content_text,$lst_index,strlen($keyword),$tags)){

                    $lst_index = stripos($content_text,$keyword,$lst_index+strlen($keyword));
                }
                if($lst_index)
                    $matching = true;
            }



			if($matching){ //匹配不被包含的关键词
                $replace_str = "<a href=".$v['target_url']." target='_blank' rel='noopener noreferrer' >".$keyword."</a>";

//var_dump(stripos($lst_index+strlen($keyword)+4,$tags));
//stripos(substr($row->text,$lst_index),$keyword.$tags)

			    if(stripos($lst_index+strlen($keyword)+4,$tags)){ //存在锚文本，完全匹配，进行链接替换

					$crt_index = $lst_index;
					
					while($content_text[$crt_index].$content_text[$crt_index-1]!="a<" && $crt_index!=0)
						$crt_index--;

					$row->text = substr_replace($content_text,$replace_str,$crt_index-1,$lst_index-$crt_index+strlen($keyword)+5);

                }else{ //不存在锚文本，添加
                   // var_dump(substr_replace($content_text,$replace_str,$lst_index,strlen($keyword)));exit;
                    $row->text = substr_replace($content_text,$replace_str,$lst_index,strlen($keyword));

                }
                $success = 1;
			}

//file_put_contents($file_path,$row->text);

			if($v['match_state'] == 0)
                $this->update_result_text($row->alias,$keyword,$is_new,$success);

		}

	}
	
	//返回字符串索引是否被指定标签包含
	private function is_included_tags($content,$index,$len,$tags){

		$half = substr($content,$index);

		if($this->filter_h_tags($content,$index))
		    return true;

        $a_pos = stripos($half,$tags);

		if($a_pos){ //判断索引的下文是否存在a标签，若不存在返回false

            $pro_tag = substr(str_replace('/','',$tags),0,-1);
            $next_tag = substr($content,$index+$len,abs($a_pos-$len));

            if(is_numeric(stripos($next_tag,$pro_tag))) //下文包含同时包含前闭合，返回false
                return false;

            $pro_index = $index;
            while (substr($content,$pro_index,2)!=$pro_tag){
                $pro_index--;
                if($pro_index==0) //判断上文是否有前闭合，若没有则返回ture （跳过）
                    return true;
            }
            //已经存在a标签且前闭合的情况：若a标签索引与关键词索引相等且关键词有后闭合则返回false，否则返回true
            if($a_pos+$index==$len+$index && $content[$index-1]==">")
                return false;
            return true;
		}else{
			return false;
		}
	}

	//过滤h标签
    private function filter_h_tags($content,$index){

        $pro_str =substr($content,0,$index);

        for($i =1; $i<7 ;$i++){
            $pro_count = substr_count($pro_str,"<h".$i.">");
            $end_count = substr_count($pro_str,"</h".$i.">");

            if($pro_count == $end_count){
                continue;
            }else{
                return true;
            }
        }

        return false;
    }

    //过滤标签内文本
    private function filter_inner_tags($content,$index){
        $pro_str =substr($content,0,$index);

        $pro_count = substr_count($pro_str,'<');
        $end_count = substr_count($pro_str,'>');

        return $pro_count==$end_count;
    }


    //过滤其他标签
    private function filter_other_tags($content,$lst_index,$tags){

        $start = stripos($content,$tags);
       // var_dump($lst_index);var_dump($start);
        while ($start){
            $end = 0; $i = $start;
            while (true){
                $lst = $end;

                $end = stripos($content,'</div>',$i);
                $current_str = substr($content,$start,$end-$i);

                $pro_count = substr_count($current_str,'<div');
                $close_count = substr_count($current_str,'</div>');

                $i = $end+1;
                if($pro_count==$close_count) {
                    break;
                }
            }

            if($lst_index>$start && $lst_index<$lst){
                return false;
            }else{
                return true;
            }
            $start =  stripos($content,$tags,$end);
        }


        return true;
    }

    //获取连接
    public function get_efs_url($id,$catid,$lang){
        include_once JPATH_ROOT . '/components/com_content/helpers/route.php';
        $url = ContentHelperRoute::getArticleRoute( $id, $catid, $lang);
        $res = substr(juri::root(), 0, -1) . JRoute::link('site', $url);
        return $res;
    }

    //检查发布状态
    private function check_init_state($alias){

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('article_alias'))
            ->select($this->db->quoteName('match_state'))
            ->select($this->db->quoteName('published'))
            ->from($this->db->quoteName('#__anchor','t1'))
            ->where($this->db->quoteName('article_alias') . ' = ' . "'".$alias."'")
            ->where($this->db->quoteName('published') . ' = 0');

        $this->db->setQuery($query);
        //echo($query->__toString());exit;
        $arr = $this->db->loadAssocList();

        if(!empty($arr)){
            $conditions = [
                $this->db->quoteName('article_alias') . ' = ' . "'".$alias."'",
                $this->db->quoteName('published') . ' = 1'
            ];
            $query_init = $this->db->getQuery(true)
                ->update($this->db->quoteName('#__anchor'))
                ->set($this->db->quoteName('match_state'). ' = 0')
                ->where($conditions);
            $this->db->setQuery($query_init);
            return true;
        }

        return false;

    }

    //更新匹配状态
    private function update_result_text($alias,$keyword,$is_new,$status){

        $field = $is_new?'new_keyword':'keyword';

        $query = $this->db->getQuery(true);

        $conditions = [
            $this->db->quoteName('article_alias') . ' = '."'".$alias."'",
            $this->db->quoteName($field) . ' = ' ."'".$keyword."'"
        ];

        $query->update($this->db->quoteName('#__anchor'))
            ->set($this->db->quoteName('match_state') . ' = ' . "'".$status."'")
            ->where($conditions);

        $this->db->setQuery($query);

        //echo($query->__toString());exit;
        $this->db->execute();

    }



}
