jQuery(document).ready(function( $ ) {

  /**
   *
   * @param value
   * @param precision
   * @returns {number}
   */
  function round(value, precision) {
    var multiplier = Math.pow(10, precision || 0);
    return Math.round(value * multiplier) / multiplier;
  }

  // Check if form exist
  // we don't want to have any unnecessary code
  if ( $('#mf-wpf-tags-wrapper').length > 0 ) {

    // Loader
    var mf_loader = $('#mf-loader');

    // Plugin path
    var plugin_path = $('#mf_plugin_path').val();

    // Step 1
    var button_get_pages_id = $('#mf_get_all_pages');
    var all_pages_id = $('#mf_all_pages_id'); // values will be added in input separated with comma
    var step_1_message = $('#mf-step-1-message');
    var selected_post_types = $('#mf_selected_post_types');
    var if_post_types_selected = false;

    // Step 2
    var button_add_tags = $('#add_tags_to_pages_ajax');
    var button_add_tags_action = false; // if button is already clicked
    var process_of_adding_tags = $('#mf-the-process-of-adding-tags');
    var display_message = $('#mf-the-display-message');
    var progress_bar = $('#mf-progress-bar');

    // Output
    var mf_wpf_output = $('#mf-wpf-protecte-content-output');


    // Step 1 - Get all pages
    button_get_pages_id.on('click', function (e) {
      e.preventDefault();


      // get all selected post types
      var checked_post_types = [];
      $('.mf_single_post_type:checkbox:checked').each(function () {
        checked_post_types.push($(this).val())
      });

      // If user not selected least one post type
      if ( checked_post_types.length === 0 ) {
        alert('You have to select at least one post type!');
        return;
      } else {
        if_post_types_selected = true;
      }



      // if pages already added then just return
      // disabled for now, because maybe someone wants to add more post types or delete some of them
      if ( all_pages_id.val() !== '' ) {
        //return;
      }

      jQuery.ajax({
        type: 'post',
        dataType: 'json',
        data: {action:'wpfGetAllPagesId', mf_post_types:checked_post_types},
        url: wpp.ajax_url,
        beforeSend: function () {
          mf_loader.attr('style', 'display: block;');
        },
        success: function( r ){
          all_pages_id.val(r.content_id);
          selected_post_types.html('<strong>Selected</strong>: ' + r.selected_pt);
        },
        complete: function () {
          //button_get_pages_id.addClass('disabled');
          button_add_tags.removeClass('disabled');
          mf_loader.attr('style', 'display: none;');
          step_1_message.html('<img id="mf-success-img" src="'+plugin_path+'assets/admin/images/success.png">');
        },
        error: function (jqXHR, exception) {
          console.log(jqXHR, exception);
        }
      });
    });

    button_add_tags.on('click', function (e) {
      e.preventDefault();

      // If user has finished first step but there is no content to be protected
      // show them message about that and return
      if ( all_pages_id.val() === '' && if_post_types_selected === true ) {
        alert('We couldn\'t find any content in selected post type');
        return;
      }

      $(this).addClass('disabled'); // disable button when process is started

      // Just to be sure that nobody will play with removing disabled class and start process again
      if ( button_add_tags_action === true ) {
        return;
      }

      if ( all_pages_id.val() === '' ) {
        alert('Please finish the first step!');
        return;
      }

      // show the output message of process of adding tags
      process_of_adding_tags.attr('style', 'display: block;');

      // get all pages id and convert it to array
      var split_all_page_id = all_pages_id.val().split(',');
      var all_pages_length = split_all_page_id.length;
      var z = 0;
      var progress_bar_width = 0;

      // This function will add tags to the page
      function add_tags_1() {
        console.log(z);
        jQuery.ajax({
          type: 'post',
          dataType: 'html',
          data: {action:'wpfContentProtect', page_id:split_all_page_id[z]},
          url: wpp.ajax_url,
          beforeSend: function () {
            //mf_loader.attr('style', 'display: block;');
          },
          success: function( r ){
            mf_wpf_output.append(r);
          },
          complete: function () {
            // When is completed all, call the another function for adding tags
            add_tags_2();
          },
          error: function (jqXHR, exception) {
            console.log(jqXHR, exception);
          }
        });
      }

      // This function will check the number of current index (variable: z)
      // It will return false and show success message if everything is finished
      // Or it will call the first method ( add_tags_1() ) and increase current index (variable: z)
      function add_tags_2() {

        // Progress bar functionality
        var new_index = z + 1;
        var new_progress_width = progress_bar_width + (( new_index / parseInt(all_pages_length) ) * 100);
        //var new_progress_width = progress_bar_width + (( new_index / 16 ) * 100); // debug

        // Sometimes the final number can be like 100.2 100.3 so we want to show 100% on the end
        if ( new_progress_width > 100 ) new_progress_width = 100;

        progress_bar.animate({width: new_progress_width+'%'}); // move progress bar
        progress_bar.text(round(new_progress_width, 1)+ '%'); // show current status in the percentage

        // When it's done, set progress bar background to green
        if ( new_progress_width === 100 ) {
          progress_bar.css({'background':'#41ad49'});
        }

        // If completed change the loading image with success image
        if ( z ==  all_pages_length) {
        //if ( z ==  15) { // debug
          display_message.html('<img id="mf-success-img" src="'+plugin_path+'assets/admin/images/success.png">');
          return false;
        }

        z++;
        add_tags_1();
      }

      // Call the method for adding tags to the pages
      add_tags_1();
      button_add_tags_action = true;
    });

  }
});