<div class="field">
  <div id="role_magick_owner_id_label" class="two columns alpha">
    <label for="role_magick_owner_id">Assign Ownership To:</label>
  </div>
  <div class="inputs five columns omega">
    <p class="explanation">
      Assign ownership of this collection to a different user. When the user has
      a partner role, this will restrict some interactions they would normally
      have access to as an owner with the role of contributor.
    </p>
    <?php echo $this->formSelect('owner_id', $owner_id, null, $options); ?>
  </div>
</div>
