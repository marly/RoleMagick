<?php
/**
 * @package RoleMagick
 * @copyright Copyright 2014, Marly Wilson
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3 or any later version
 */

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
    // Nothing to do yet.
  }

  public function hookUninstall($args)
  {
    // Downgrade Partners to Researchers
    $partners = $this->_$db->getTable('User')->findBy(array('role'=>'partner'));
    foreach($partners as $partner) {
      $partner->role = 'researcher';
      $partner->save();
    }
  }

  public function hookDefineAcl($args)
  {
    $acl = $args['acl'];
    $acl->addRole(new Zend_Acl_Role('partner'), 'researcher');
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
    $user_table = $this->_db->getTable('User');
    $options = $this->findUserPairsForSelectForm();
    $options = array('0' => 'No owner') + $options;
    $owner = $user_table.find($args['collection']->owner_id);
    $ownerId = $owner ? $owner->id : 0;
    $tabs['Ownership'] = get_view()->partial(
      'collections/role-magick-owner-form.php',
      array('options' => $options, 'owner_id' => $ownerId)
    );
    return $tabs;
  }

  public function findUserPairsForSelectForm($padding = '-'){
    /**
     * A list of user ids and names (or email addresses) to 
     * populate the select list for the owner_id.
     */

    $options = array();
    $user_table = $this->_db->getTable('User');

    foreach ($user_table.fetchAll() as $user) {
      $options[$user['id']] = $user['name'] ? $user['name'] : $user['username'];
    }

    return $options;
  }

}
