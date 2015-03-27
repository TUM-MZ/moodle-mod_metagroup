window.metaLink = jQuery.noConflict(true);

(function($){
    $(function(){
        console.log("meta link");
        //$('#id_groups').prop( "disabled", true );
        $("#id_link").on("change", function(e) {
            loadGroups(this);
        });
    });
})(metaLink);

function loadGroups(elem)
{
    $.ajax({
        url: M.cfg['wwwroot'] + '/enrol/metagroup/groups.json.php',
        type: 'POST',
        data: {
            courseid: $(elem).val(),
            sesskey: $('form :input[name=sesskey]').val()
        },
        success: function(res) {
            var groups = JSON.parse(res);
            if(Object.keys(groups).length > 0){
                //$('#id_courseg').prop( "disabled", false );
                $('#id_groups')     //initialize select element
                    .find('option')
                    .remove()
                    .end()
                    .append('<option value="0">All</option>')
                    .val('0')
                ;
            }
            else{
                //$('#id_courseg').prop( "disabled", true );
                $('#id_groups')     //initialize select element
                    .find('option')
                    .remove()
                ;
            }
            Object.keys(groups).map(function(key){
                $('#id_groups')
                    .append($("<option></option>")
                        .attr("value",groups[key].id)
                        .text(groups[key].name))
                ;
            });
        }
    });
}
