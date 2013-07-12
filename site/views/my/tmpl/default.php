<?php
/**
 * @version 1.9
 * @package JEM
 * @copyright (C) 2013-2013 joomlaeventmanager.net
 * @copyright (C) 2005-2009 Christoph Lukes
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<div id="jem" class="jem_jem">
<p class="buttons">
	<?php
		echo JEMOutput::publishbutton();
		echo JEMOutput::unpublishbutton();
		echo JEMOutput::trashbutton();
	?>
</p>
<?php if ($this->params->def( 'show_page_title', 1 )) : ?>

    <h1 class="componentheading">
		<?php echo $this->escape($this->pagetitle); ?>
	</h1>

<?php endif; ?>

<!--table-->

<?php 
	echo $this->loadTemplate('events'); 
?>


<!--footer-->

<p class="copyright">
  <?php echo JEMOutput::footer( ); ?>
</p>

</div>