<?php
/**
 * @package RoleMagick
 * @copyright Copyright 2014, Marly Wilson
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

define('ROLE_MAGICK_PLUGIN_DIR', PLUGIN_DIR . '/RoleMagick');
require_once(ROLE_MAGICK_PLUGIN_DIR . '/libraries/RoleMagick_AssertPartner.php');

class RoleMagickPlugin extends Omeka_Plugin_AbstractPlugin
{
  protected $_hooks = array(
    'install', 
    'uninstall', 
    'define_acl',
    'config',
    'config_form',
    'before_save_collection',
    'initialize'
    //'admin_append_to_collections_form'
  );

  protected $_filters = array(
    'admin_collections_form_tabs'
  );

  public function hookInstall()
  {
    $db = $this->_db;
  }

  public function hookUninstall($args)
  {
    $db = $this->_db;

    // Downgrade Partners to Researchers
    $partners = $db->getTable('User')->findBy(array('role' => 'partner'));
    foreach($partners as $partner) {
      $partner->role = 'researcher';
      $partner->save();
    }
  }

  public function hookDefineAcl($args)
  {
    $acl = $args['acl'];
    $acl->addRole(new Zend_Acl_Role('partner'), 'contributor');

    // Partners cannot delete their own items. 
    $acl->deny('partner', 'Items', array('delete-confirm', 'deleteSelf'));

    // Partners can add and edit items in collections they own or are 
    // partnered with.
    $acl->allow('partner', 'Items', array('add', 'edit', 'tag'),  new RoleMagick_AssertPartner());
    // Partners cannot add or delete collections.
    $acl->deny('partner', 'Collections', array('add', 'delete-confirm', 'deleteSelf'));
    // Partners can edit collections they own or are partnered with.
    $acl->allow('partner', 'Collections', array('edit'),  new RoleMagick_AssertPartner());
    
  }

  public function hookConfig($args)
  {
  }

  public function hookConfigForm()
  {
  }

  public function hookBeforeSaveCollection($args)
  {
  }

  public function hookInitialize()
  {
  }

  public function filterAdminCollectionsFormTabs($tabs, $args)
  {
    // Only show if current user is admin or above.
    $currentRole = current_user()->role;
    if $currentRole == 'admin' || $currentRole == 'super') {
      $user_table = $this->_db->getTable('User');
      $options = $this->findUserPairsForSelectForm();
      $options = array('0' => 'No owner') + $options;
      $owner = $user_table->find($args['collection']->owner_id);
      $ownerId = $owner ? $owner->id : 0;
      $tabs['Ownership'] = get_view()->partial(
        'collections/role-magick-owner-form.php',
        array('options' => $options, 'owner_id' => $ownerId)
      );
    }
    return $tabs;
  }

  public function findUserPairsForSelectForm($padding = '-'){
    /**
     * A list of user ids and names (or email addresses) to 
     * populate the select list for the owner_id.
     */

    $options = array();
    $user_table = $this->_db->getTable('User');
    $assignable_users = $user_table->findAll();

    foreach ($assignable_users as $user) {
      $options[$user['id']] = $user['name'] ? $user['name'] : $user['username'];
    }

    return $options;
  }

}
