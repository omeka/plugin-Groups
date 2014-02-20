if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    clickedElement: null,
    
    flag: function() {
        Omeka.Groups.clickedElement = this;
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post('groups/flag/' + groupId, {'groupId' : groupId}, Omeka.Groups.flagResponse);        
    },

    flagResponse: function(responseJson, status, jqXHR) {
        if(responseJson.status === 'ok') {
            var html = '<li id="group-' + responseJson.id + '" class="flagged" style="color: rgb(78, 113, 129); cursor: pointer;" >Unflag</li>';
            jQuery(Omeka.Groups.clickedElement).replaceWith(html);
        }        
    }
};

(function($) {
    $(document).ready(function() {
        $('.flag').click(Omeka.Groups.flag);
    });
})(jQuery)
