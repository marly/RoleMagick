<?php
/**
 * RoleMagick - AssertPartner
 *
 */

/**
 * Assertion to take into account ownership and partnership of a User when
 * managing a collection and the items within it.
 */

class RoleMagick_AssertPartner implements Zend_Acl_Assert_Interface
{
  /**
   * Assert whether the ACL should allow access.
   */
  public function assert(Zend_Acl $acl,
                         Zend_Acl_Role_Interface $role = null,
                         Zend_Acl_Resource_Interface $resource = null,
                         $privilege = null)
  {
    $allPriv = $privilege . 'All';
    $selfPriv = $privilege . 'Self';

    // Allow if user is partnered
    if (($role instanceof User) && ($resource instanceof Omeka_Record_AbstractRecord)) {
      $allowed = ($acl->isAllowed($role, $resource, $allPriv)
                  || $this->_userIsPartnered($role, $resource));
    } else {
      $allowed = false;
    }
    return $allowed;
  }

  private function _userOwnsRecord($user, $record)
  {
    return $record->isOwnedBy($user);
  }

  private function _userIsPartnered($user, $record)
  {
    if ($record instanceof Collection) {
      $collection_id = $record->id;
    } else if ($record instanceof Item) {
      $collection_id = $record->collection_id;
    }

    if (!empty($collection_id) && 
        $collection = get_db()->getTable('Collection')->find($collection_id)) {
      // if owns collection, then true
      return $this->_userOwnsRecord($user, $collection);
    } else {
      return false;
    }
  }

}

