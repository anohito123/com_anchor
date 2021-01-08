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
 * Methods supporting a list of redirect links.
 *
 * @since  1.6
 */
class AnchorModelLinks extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'anchor_id', 'a.anchor_id',
				'state', 'a.state',
				'inner_url', 'a.inner_url',
				'target_url', 'a.target_url',
				'article_alias', 'a.article_alias',
				'keyword', 'a.keyword',
				'created_date', 'a.created_date',
				'published', 'a.published',
                'match_state', 'a.match_state',
                //'header', 'a.header', 'http_status',
			);
		}

		parent::__construct($config);
	}
	/**
	 * Removes all of the unpublished redirects from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   3.5
	 */
	public function purge()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);

		$query->delete('#__anchor')->where($db->qn('published') . '= 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.inner_url', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.http_status', $this->getUserStateFromRequest($this->context . '.filter.http_status', 'filter_http_status', '', 'cmd'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_anchor');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.http_status');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	public function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);


		$query->from($db->quoteName('#__anchor', 'a'));

		// Filter by published state
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.published') . ' = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where($db->quoteName('a.published') . ' IN (0,1)');
		}

		// Filter the items over the HTTP status code header.
//		if ($httpStatusCode = $this->getState('filter.http_status'))
//		{
//			$query->where($db->quoteName('a.header') . ' = ' . (int) $httpStatusCode);
//		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');
        $match_state = $this->getState('filter.match_state');
        $date_type = $this->getState('filter.date_type');
        $stime = $this->getState('filter.stime');
        $etime = $this->getState('filter.etime');

        if($date_type!='0' && $stime!=null && $etime!=null){
            $time_field = $date_type=='1'?'created_date':'modified_date';
            $query->where($db->quoteName($time_field) . ' > ' ."'".$stime."'")
                ->where($db->quoteName($time_field) . ' < ' ."'".$etime."'");
        }

        if (!empty($search))
		{
		    $flag = strpos($search,':');

		    if($flag){
                $column_arr = ['anchor_id','keyword','new_keyword','remark'];

                $input = explode(':',$search);

                if(in_array($input[0],$column_arr)){
                    $query->where($db->quoteName($input[0]) . ' = ' ."'".$input[1]."'");
                }
            }
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where(
					'(' . $db->quoteName('inner_url') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('target_url') . ' LIKE ' . $search .
                    ' OR ' . $db->quoteName('keyword') . ' LIKE ' . $search .
                    ' OR ' . $db->quoteName('new_keyword') . ' LIKE ' . $search .
                    ' OR ' . $db->quoteName('remark') . ' LIKE ' . $search .
					' OR ' . $db->quoteName('article_alias') . ' LIKE ' . $search . ')'
				);
			}
		}

		// Add the list ordering clause.
        if($match_state!=-1 && $match_state!=null){
            $query->where($db->quoteName('match_state') . ' = ' .$match_state);
        }

       //echo($query->__toString());exit;
		$query->order($db->escape($this->getState('list.ordering', 'a.inner_url')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}

	/**
	 * Add the entered URLs into the database
	 *
	 * @param   array  $batch_urls  Array of URLs to enter into the database
	 *
	 * @return boolean
	 */
	public function batchProcess($batch_urls)
	{


		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$params = JComponentHelper::getParams('com_anchor');
		$state  = (int) $params->get('defaultImportState', 0);

		$columns = array(
			$db->quoteName('inner_url'),
			$db->quoteName('target_url'),
			$db->quoteName('article_alias'),
			$db->quoteName('remark'),
			$db->quoteName('keyword'),
			$db->quoteName('published'),
			$db->quoteName('created_date')
		);

		$query->columns($columns);

		foreach ($batch_urls as $batch_url)
		{
			$inner_url = $batch_url[0];

			// Destination URL can also be an external URL
			if (!empty($batch_url[1]))
			{
				$target_url = $batch_url[1];
			}
			else
			{
				$target_url = '';
			}

			$query->insert($db->quoteName('#__anchor'), false)
				->values(
					$db->quote($inner_url) . ', ' . $db->quote($target_url) . ' ,' . $db->quote('') . ', ' . $db->quote('') . ', 0, ' . $state . ', ' .
					$db->quote(JFactory::getDate()->toSql())
				);
		}

		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
