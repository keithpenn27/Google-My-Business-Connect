var gmbcReviews;

jQuery(document).ready( function($) {

  // The GMBC reviews object
  gmbcReviews = {

    syncBtn: "#gmbc-sync-reviews",
    spinner: "#gmbc-reviews-spinner",
    syncAction: "sync_reviews",
    errorMessageContainer: "#gmbc-error-message",

    // Instantiate the object
    init: function() {
      $(gmbcReviews.syncBtn).on( 'click', gmbcReviews.sendAjax);
    },

    // Send the ajax call
    sendAjax: function() {

      // Fade in the spinner
      $(gmbcReviews.spinner).fadeIn(300);
    
      // Ajax atts to send
      var data = {
        'action' : gmbcReviews.syncAction,
        'is_ajax'   : true
      };
    
      // ajax call to trigger the sync reviews method when the "Sync Reviews" button is clicked.
      $.post( ajaxurl, data, function( response ) {
        if( typeof response.errors != 'undefined' && response.errors != null ) {

          // We have an error in the ajax response
          $(response.errors).each( function( ind ) {
            $(gmbcReviews.errorMessageContainer).html( 
              '<p>' + response.message + '</p>' +
              '<span>Error: ' + response.errors[ind].message + '</span>'
              );
          });
        } else {

          // No error, so log and reload the page to display the reviews
          console.log( response.message );
          location.reload();
        }
  
      })
      .complete( function() {

        // Fade out the spinner
        $(gmbcReviews.spinner).fadeOut(300);
      });

    }
  }

  // Try to instantiate the object
  try {
    gmbcReviews.init();
  } catch (ex) {
    console.log( 'Error Caught: ', ex );
  }
});
