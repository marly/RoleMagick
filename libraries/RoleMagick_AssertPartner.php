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

    // Only applies to Items and Collections
    if ($resource instanceof Item || $resource instanceof Collection) {
      $allowed = $acl->isAllowed($role, $resource, $allPriv)
             || ($acl->isAllowed($role, $resource, $selfPriv)
                 && $this->_userIsPartnered($role, $resource));
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

    if (empty($collection_id)) {
      return false;
    // if owns collection, then true
    } else if ($collection = $this->db->getTable('Collection')->find($collection_id)) {
      return $this->userOwnsRecord($user, $collection);
    } else {
      return false;

    }
  }

}

