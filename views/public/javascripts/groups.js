if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    addItemToGroup: function() {

        splitId = this.id.split('-');
        splitUrl = window.location.pathname.split('/');
        groupId = splitId[splitId.length - 1];
        itemId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/add-item', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.addItemResponse);
    },

    addItemResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        if(responseJson.groupId) {
            jQuery('li#groups-id-' + responseJson.groupId).html('Successfully added');
        }
    },

    join: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/join/' + groupId, null, Omeka.Groups.joinResponse);
    },

    joinResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        window.location.reload(true);
    },

    quit: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/quit/' + groupId, null, Omeka.Groups.quitResponse);
    },

    quitResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        window.location.reload(true);
    },

    request: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/request/' + groupId, null, Omeka.Groups.requestResponse);
    },

    requestResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        if(responseJson.status === 'ok') {
            html = "<p class='groups-pending'>Membership request is pending</p>";
            jQuery('.groups-request-button').replaceWith(html);
        }
    },

    approveRequest: function() {
        splitId = this.parentNode.id.split('-');
        userId = groupId = splitId[splitId.length - 1];
        splitUrl = window.document.URL.split('/');
        groupId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/approve-request/', {'groupId': groupId, 'userId':userId}, Omeka.Groups.approveRequestResponse);        
        
    },

    approveRequestResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);        
        window.location.reload(true);
    },
    
    filterGroups: function() {
        splitId = this.id.split('-');
        itemId = splitId[splitId.length -1];
        groupSelector = 'li#groups-comment-group-' + itemId;
        jQuery(groupSelector).closest('div.comment').toggle('fast');
    },
    
    
    
    toggleBlocking: function() {
        inputClasses = jQuery(this).attr('class');
        split = inputClasses.split(' ');
        inputClass = split[split.length -1];
        value = jQuery("." + inputClass + ":checked").val();
        blockingDivSelector = '#groups-block-' + inputClass;
        if(value == 'decline') {
            jQuery(blockingDivSelector).show('fast');    
        } else {
            jQuery(blockingDivSelector).hide('fast');
        }
    }
    
};

jQuery(document).ready(function() {
    jQuery('li.groups-item-add').click(Omeka.Groups.addItemToGroup);
    jQuery('ul#groups-group-list li').click(Omeka.Groups.filterGroups);
    jQuery('input.groups-invitation-action').click(Omeka.Groups.toggleBlocking);
});


