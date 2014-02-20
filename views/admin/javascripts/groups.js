if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    flag: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post('groups/flag/' + groupId, {'groupId' : groupId}, Omeka.Groups.flagResponse);        
    },

    flagResponse: function(responseJson, status, jqXHR) {
        if(responseJson.status === 'ok') {
            var replaceEl = jQuery('#group-' + responseJson.id);
            var newEl = jQuery('<li id="group-' + responseJson.id + '" class="flagged" style="color: rgb(78, 113, 129); cursor: pointer;" >Unflag</li>');
            replaceEl.replaceWith(newEl);
            newEl.click(Omeka.Groups.unflag);
            newEl.parents('tr').addClass('flagged');
        }        
    },
    
    unflag: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post('groups/unflag/' + groupId, {'groupId' : groupId}, Omeka.Groups.unflagResponse);        
    },

    unflagResponse: function(responseJson, status, jqXHR) {
        if(responseJson.status === 'ok') {
            var replaceEl = jQuery('#group-' + responseJson.id);
            var newEl = jQuery('<li id="group-' + responseJson.id + '" class="flag" style="cursor: pointer;" >Flag</li>');
            replaceEl.replaceWith(newEl);
            newEl.click(Omeka.Groups.flag);
            newEl.parents('tr').removeClass('flagged');
        }        
    },
};

(function($) {
    $(document).ready(function() {
        $('.flag').click(Omeka.Groups.flag);
        $('.flagged').click(Omeka.Groups.unflag);
    });
})(jQuery)
