<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Link Table for Redirect.
 *
 * @since  1.6
 */
class AnchorTableLink extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database object.
	 *
	 * @since   1.6
	 */
	public function __construct($db)
	{
		parent::__construct('#__anchor', 'anchor_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function check()
	{
		$this->inner_url = trim(rawurldecode($this->inner_url));
		$this->target_url = trim(rawurldecode($this->target_url));
        $this->keyword = trim($this->keyword);
        $match = "/^(https?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/";

         $alias = substr($this->inner_url,strripos($this->inner_url,'/')+1);

        if(substr($alias,-5)=='.html'){
             $alias = substr($alias,0,-5);
        }
        $this->article_alias = $alias;



        if(!preg_match($match,$this->inner_url) || !preg_match($match,$this->target_url)){
            $this->setError(JText::_('链接格式错误'));

            return false;
        }
		// Check for valid name.
		if (empty($this->inner_url))
		{
			$this->setError(JText::_('无效字段'));

			return false;
		}

		// Check for NOT NULL.
		if (empty($this->article_alias))
		{
			$this->article_alias = '';
		}

		// Check for valid name if not in advanced mode.
		if (empty($this->target_url) && JComponentHelper::getParams('com_anchor')->get('mode', 0) == false)
		{
			$this->setError(JText::_('无效字段'));

			return false;
		}
//		elseif (empty($this->target_url) && JComponentHelper::getParams('com_redirect1')->get('mode', 0) == true)
//		{
//			// Else if an empty URL and in redirect mode only throw the same error if the code is a 3xx status code
//			if ($this->header < 400 && $this->header >= 300)
//			{
//				$this->setError(JText::_('COM_REDIRECT_ERROR_DESTINATION_URL_REQUIRED'));
//
//				return false;
//			}
//		}

		// Check for duplicates
		if ($this->inner_url == $this->target_url)
		{
			$this->setError(JText::_('当前链接和目标链接不能相同'));

			return false;
		}

        $this->match_state=0;
//        JLoader::register('AnchorHelper', JPATH_ADMINISTRATOR . '/components/com_anchor/helpers/anchor.php');
//        $check_arr = AnchorHelper::get_anchor_arr();
//
//        $three_keys = ['inner_url'=>$this->inner_url,'keyword'=>$this->keyword,'target_url'=>$this->target_url];

//        if(in_array($three_keys,$check_arr)){
//            $this->setError(JText::_('$three_keys'));
//
//            return false;
//        }

        /*
        $db = $this->getDbo();

        // Check for existing name

        $query = $db->getQuery(true)
            ->select($db->quoteName('anchor_id'))
            ->select($db->quoteName('inner_url'))
            ->from('#__anchor')
            ->where($db->quoteName('inner_url') . ' = ' . $db->quote($this->inner_url));
        $db->setQuery($query);
        $urls = $db->loadAssocList();

        foreach ($urls as $url)
        {
            if ($url['inner_url'] === $this->inner_url && (int) $url['anchor_id'] != (int) $this->anchor_id)
            {
                $this->setError(JText::_('COM_REDIRECT_ERROR_DUPLICATE_OLD_URL'));

                return false;
            }
        }
        */

		return true;
	}

	/**
	 * Overriden store method to set dates.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate()->toSql();

		$this->modified_date = $date;

		if (!$this->anchor_id)
		{
			// New record.
			$this->created_date = $date;
		}

		return parent::store($updateNulls);
	}
}
