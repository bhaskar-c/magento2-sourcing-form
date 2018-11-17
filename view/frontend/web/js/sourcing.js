alert('Helllo Bonga');
// When the DOM has loaded, init the form link.
if (typeof $ == 'undefined') {
   var $ = jQuery;
}
$(
    function(){
        // Get the add new upload link.
        var jAddNewUpload = $( "#add-file-upload" );

        // Hook up the click event.
        jAddNewUpload
            .attr( "href", "javascript:void( 0 )" )
            .click(
                function( objEvent ){
                      
                      AddNewUpload();

                    // Prevent the default action.
                    objEvent.preventDefault();
                    return( false );
                }
                )
        ;

    }
    )


// This adds a new file upload to the form.
function AddNewUpload(){
    // Get a reference to the upload container.
    var jFilesContainer = $( "#filesContainer" );
    // Get the file upload template.
    var jUploadTemplate = $( "#element-templates" );
    // Duplicate the upload template. This will give us a copy
    // of the templated element, not attached to any DOM.
    var jUpload = jUploadTemplate.clone();
    // At this point, we have an exact copy. This gives us two
    // problems; on one hand, the values are not correct. On
    // the other hand, some browsers cannot dynamically rename
    // form inputs. To get around the FORM input name issue, we
    // have to strip out the inner HTML and dynamically generate
    // it with the new values.
    var strNewHTML = jUpload.html();
    // Now, we have the HTML as a string. Let's replace the
    // template values with the correct ones. To do this, we need
    // to see how many upload elements we have so far.
    var intNewFileCount = (jFilesContainer.find( ".newlyAdded" ).length + 1);
    // Set the proper ID.
    jUpload.attr( "id", ("file" + intNewFileCount) );
    // Replace the values.
    strNewHTML = strNewHTML
        .replace(
            new RegExp( "::FIELD2::", "i" ),
            intNewFileCount
            )
        .replace(
            new RegExp( "::FIELD3::", "i" ),
            ("sourcingFileAttachment" + intNewFileCount)
            )
        .replace(
           "class=\"templaterow\"",
           "class=\"newlyAdded\""
            )    
    ;

    // Now that we have the new HTML, we can replace the
    // HTML of our new upload element.
    jUpload.html( strNewHTML );

    // At this point, we have a totally intialized file upload
    // node. Let's attach it to the DOM.
    jFilesContainer.append( jUpload );
    $(".newlyAdded").removeAttr("style");
    $('input[name="attachmentCount"]').val(intNewFileCount);


}

