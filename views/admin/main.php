<div id="mf-loader" style="display: none;">
  <img id="md-loader-1" src="<?php echo MF_PLUGIN_PATH.'assets/admin/images/loader.gif'; ?>">
</div>

<div id="mf-wpf-tags-wrapper" class="mf-wpf-content-protect">
	<h2 class="mf-tags-title"><?php _e('Protect your content from DAP to WP Fusion','mf-wpfprotect') ?></h2>
  
  <div class="mf-wpf-step-1">
    <h3><?php _e('Step 1 - Select post types of content that you want to protect','mf-wpfprotect'); ?></h3>
    
    <div class="mf_all_post_types">
		    <?php foreach ($get_all_post_types as $pt): ?>
          <label class="mf_get_all_pt" for="mf_if-<?php echo $pt['slug'] ?>">
            <input id="mf_if-<?php echo $pt['slug'] ?>" type="checkbox" name="get_post_types[]" class="mf_single_post_type" value="<?php echo $pt['slug'] ?>"><?php echo $pt['name']; ?>
          </label>
        <?php endforeach; ?>
    </div>
    
    <button id="mf_get_all_pages" class="button button-primary">Prepare content</button><span id="mf-step-1-message"></span>
    <input type="hidden" id="mf_all_pages_id" value="">
    <input type="hidden" id="mf_plugin_path" value="<?php echo MF_PLUGIN_PATH; ?>">
    
    <p id="mf_selected_post_types"></p>
  </div>


  <div class="mf-wpf-step2">
    <h3><?php _e('Step 2 - Add tags','mf-wpfprotect'); ?></h3>
    <button id="add_tags_to_pages_ajax" class="button button-primary disabled"><?php _e('Add tags to your content', 'mf-wpfprotect'); ?></button>
  </div>
  
  <div id="mf-the-process-of-adding-tags" style="display: none">
    
    <div id="mf-status">
      <p id="mf-the-display-message">
        <img id="md-loader-2" src="<?php echo MF_PLUGIN_PATH.'assets/admin/images/loader.gif'; ?>">
      </p>
      
      <p class="mf-progress-pt"><?php _e('Progress', 'mf-wpfprotect'); ?></p>
      <div class="mf-progress-bar-wrapped">
        <div id="mf-progress-bar"></div>
      </div>
      
    </div>
    <div id="mf-wpf-protecte-content-output"></div>
  </div>
 
</div>