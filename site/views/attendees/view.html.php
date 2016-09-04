<?php
/**
 * @version 2.1.7
 * @package JEM
 * @copyright (C) 2013-2016 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;


/**
 * Attendees-view
 * @todo fix view
 */
class JemViewAttendees extends JViewLegacy {

	public function display($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JemFactory::getUser();
		//redirect if not logged in
		if (!$user->get('id')) {
			$app->enqueueMessage(JText::_('COM_JEM_NEED_LOGGED_IN'), 'error');
			return false;
		}

		if ($this->getLayout() == 'print') {
			$this->_displayprint($tpl);
			return;
		}

		if ($this->getLayout() == 'addusers') {
			$this->returnto = base64_decode($app->input->get('return', '', 'base64'));
			$this->_displayaddusers($tpl);
			return;
		}

		//initialise variables
		$this->settings    = JemHelper::globalattribs();
		$this->jemsettings = JemHelper::config();

		$document	= JFactory::getDocument();
		$settings	= $this->settings;
		$params 	= $app->getParams();
		$menu		= $app->getMenu();
		$menuitem	= $menu->getActive();
		$uri 		= JFactory::getURI();

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal', 'a.flyermodal');

		// Load css
		JemHelper::loadCss('jem');
		JemHelper::loadCustomTag();

		//get vars
		$filter_order		= $app->getUserStateFromRequest('com_jem.attendees.filter_order', 'filter_order', 'u.username', 'cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest('com_jem.attendees.filter_order_Dir', 'filter_order_Dir', '', 'word');
		$filter_status		= $app->getUserStateFromRequest('com_jem.attendees.filter_status', 'filter_status', -2, 'int');
		$filter 			= $app->getUserStateFromRequest('com_jem.attendees.filter', 'filter', '', 'int');
		$search 			= $app->getUserStateFromRequest('com_jem.attendees.filter_search', 'filter_search', '', 'string');

		// Get data from the model
		$rows      	= $this->get('Data');
		$pagination = $this->get('Pagination');
		$event 		= $this->get('Event');

		// Merge params.
		// Because this view is not useable for menu item we always overwrite $params.
		$pagetitle = JText::_('COM_JEM_MYEVENT_MANAGEATTENDEES') . ' - ' . $event->title;
		$params->set('page_heading', JText::_('COM_JEM_MYEVENT_MANAGEATTENDEES')); // event title is shown separate
		//$params->set('show_page_heading', 1); // always show?
		$params->set('introtext', ''); // there can't be an introtext
		$params->set('showintrotext', 0);
		$pageclass_sfx = $params->get('pageclass_sfx');

		// Add site name to title if param is set
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$pagetitle = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $pagetitle);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$pagetitle = JText::sprintf('JPAGETITLE', $pagetitle, $app->getCfg('sitename'));
		}

		$document->setTitle($pagetitle);

		$pathway = $app->getPathWay();
		if($menuitem) {
			$pathway->setItemName(1, $menuitem->title);
		}
		$pathway->addItem('Att:'.$event->title);

		// Emailaddress
		$enableemailaddress = $params->get('enableemailaddress', 0);

		$print_link = 'index.php?option=com_jem&view=attendees&layout=print&task=print&tmpl=component&id='.$event->id;
		$backlink = 'attendees';


		//build filter selectlist
		$filters = array();
		if ($settings->get('global_regname', '1')) {
			$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_NAME'));
		} else {
			$filters[] = JHtml::_('select.option', '2', JText::_('COM_JEM_USERNAME'));
		}
		$lists['filter'] = JHtml::_('select.genericlist', $filters, 'filter', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter);

		// search filter
		$lists['search'] = $search;

		// attendee status
		$options = array(JHtml::_('select.option', -2, JText::_('COM_JEM_ATT_FILTER_ALL')),
		                 JHtml::_('select.option',  0, JText::_('COM_JEM_ATT_FILTER_INVITED')),
		                 JHtml::_('select.option', -1, JText::_('COM_JEM_ATT_FILTER_NOT_ATTENDING')),
		                 JHtml::_('select.option',  1, JText::_('COM_JEM_ATT_FILTER_ATTENDING')),
		                 JHtml::_('select.option',  2, JText::_('COM_JEM_ATT_FILTER_WAITING'))) ;
		$lists['status'] = JHtml::_('select.genericlist', $options, 'filter_status', array('class'=>'inputbox','onChange'=>'this.form.submit();'), 'value', 'text', $filter_status);

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		//assign to template
		$this->params		= $params;
		$this->lists 		= $lists;
		$this->enableemailaddress = $enableemailaddress;
		$this->rows 		= $rows;
		$this->pagination 	= $pagination;
		$this->event 		= $event;
		$this->pagetitle	= $pagetitle;
		$this->backlink		= $backlink;
		$this->print_link	= $print_link;
		$this->item			= $menuitem;
		$this->action		= $uri->toString();
		$this->pageclass_sfx = htmlspecialchars($pageclass_sfx);

		parent::display($tpl);
	}

	/**
	 * Prepares the print screen
	 *
	 * @param $tpl
	 */
	protected function _displayprint($tpl = null)
	{
		$document	= JFactory::getDocument();
		$app		= JFactory::getApplication();
		$params		= $app->getParams();
		$settings	= JEMHelper::globalattribs();

		// Load css
		JemHelper::loadCss('backend');
		JemHelper::loadCss('jem');
		JemHelper::loadCss('print');
		JemHelper::loadCustomTag();

		$document->setMetaData('robots', 'noindex, nofollow');

		// Emailaddress
		$enableemailaddress = $params->get('enableemailaddress', 0);

		$rows  	= $this->get('Data');
		$event 	= $this->get('Event');

		//assign data to template
		$this->rows 		= $rows;
		$this->event 		= $event;
		$this->enableemailaddress = $enableemailaddress;
		$this->settings		= $settings;

		parent::display($tpl);
	}

	/**
	 * Creates the output for the users select listing
	 */
	protected function _displayaddusers($tpl)
	{
		$app         = JFactory::getApplication();
		$jinput      = $app->input;
		$jemsettings = JemHelper::config();
	//	$db          = JFactory::getDBO();
		$document    = JFactory::getDocument();
		$model       = $this->getModel();
		$event       = $this->get('Event');

		// no filters, hard-coded
		$filter_order     = 'usr.name';
		$filter_order_Dir = '';
		$filter_type      = '';
		$search           = $app->getUserStateFromRequest('com_jem.selectusers.filter_search', 'filter_search', '', 'string');
	//	$limitstart       = $jinput->get('limitstart', '0', 'int');
	//	$limit            = $app->getUserStateFromRequest('com_jem.selectusers.limit', 'limit', $jemsettings->display_num, 'int');
	//	$eventId          = !empty($event->id) ? $event->id : 0;

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal', 'a.flyermodal');

		// Load css
		JemHelper::loadCss('jem');

		$document->setTitle(JText::_('COM_JEM_SELECT_USERS_AND_STATUS'));

		// Get/Create the model
	//	$model->setState('event.id', $eventId);
		$rows       = $this->get('Users');
		$pagination = $this->get('UsersPagination');

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		//Build search filter - unused
		$filters = array();
		$filters[] = JHtml::_('select.option', '1', JText::_('COM_JEM_NAME'));
		$searchfilter = JHtml::_('select.genericlist', $filters, 'filter_type', array('size'=>'1','class'=>'inputbox'), 'value', 'text', $filter_type);

		// search filter - unused
		$lists['search'] = $search;

		//assign data to template
		$this->searchfilter = $searchfilter;
		$this->lists        = $lists;
		$this->rows         = $rows;
		$this->pagination   = $pagination;
		$this->event        = $event;

		parent::display($tpl);
	}
}
?>