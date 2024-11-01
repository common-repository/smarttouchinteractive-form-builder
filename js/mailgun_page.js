 jQuery(function() {

        // capture all enter and do nothing

        jQuery('#Emails').keypress(function(e) {

         if(e.which == 13) {

            jQuery('#Emails').trigger('focusout');
            return false;
          }
        });
        // capture clicks on validate and do nothing

        jQuery("#validate_submit").click(function() {
         return false;
        });
        // attach jquery plugin to validate address

        jQuery('#Emails').mailgun_validator({
          api_key: 'pubkey-fdb54e0eb72cd7a63081271fe07e8503',
          in_progress: validation_in_progress,
          success: validation_success,
          error: validation_error,
        });
      });

      // while the lookup is performing

      function validation_in_progress() {

        jQuery('#sti-status').html('<div id="fountainTextG"><div id="fountainTextG_1" class="fountainTextG">L</div><div id="fountainTextG_2" class="fountainTextG">o</div><div id="fountainTextG_3" class="fountainTextG">a</div><div id="fountainTextG_4" class="fountainTextG">d</div><div id="fountainTextG_5" class="fountainTextG">i</div><div id="fountainTextG_6" class="fountainTextG">n</div><div id="fountainTextG_7" class="fountainTextG">g</div></div></br></br>');
      }
      // if email successfull validated

      function validation_success(data) {

        jQuery('#sti-status').html(get_suggestion_str(data['is_valid'], data['did_you_mean']));
      }
      // if email is invalid
     function validation_error(error_message) {
        jQuery('#sti-status').html(error_message);
      }
      // suggest a valid email

      function get_suggestion_str(is_valid, alternate) {
        if (is_valid) {

           jQuery('input[type="submit"]').prop('disabled', false);
          var result = '<span class="success">Email is valid.</span>';
          if (alternate) {
            result += '<span class="warning"> (Though did you mean <em>' + alternate + '</em>?)</span>';
          }
          return result
        } else if (alternate) {
          return '<span class="warning">Did you mean <em>' +  alternate + '</em>?</span>';
        } else {

            jQuery('input[type="submit"]').prop('disabled', true);
          return '<span class="error">Email is invalid.</span>';
        }

      }