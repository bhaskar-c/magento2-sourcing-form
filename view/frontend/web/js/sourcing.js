if (typeof $ == 'undefined') {
   var $ = jQuery;
}
$(function(){
        var jAddNewUpload = $( "#add-file-upload" );
        jAddNewUpload
            .attr( "href", "javascript:void( 0 )" )
            .click(function( objEvent ){ AddNewUpload(); objEvent.preventDefault(); return( false );});
            })

function AddNewUpload(){
    var jFilesContainer = $( "#filesContainer" );
    var jUploadTemplate = $( "#element-templates" );
    var jUpload = jUploadTemplate.clone();
    var strNewHTML = jUpload.html();
    var intNewFileCount = (jFilesContainer.find( ".newlyAdded" ).length + 1);
    jUpload.attr( "id", ("file" + intNewFileCount) );
    strNewHTML = strNewHTML
        .replace( new RegExp( "::FIELD2::", "i" ), intNewFileCount)
        .replace( new RegExp( "::FIELD3::", "i" ), ("sourcingFileAttachment" + intNewFileCount))
        .replace( "class=\"templaterow\"", "class=\"newlyAdded\"");
    jUpload.html( strNewHTML );
    jFilesContainer.append( jUpload );
    $(".newlyAdded").removeAttr("style");
    $('input[name="attachmentCount"]').val(intNewFileCount);
}



