if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    addItemToGroup: function() {

        splitId = this.id.split('-');
        splitUrl = window.document.URL.split('/');
        groupId = splitId[splitId.length - 1];
        itemId = splitUrl[splitUrl.length - 1];
        jQuery.post('/commons/groups/add-item', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.addItemResponse);
    },

    addItemResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        //notify on the item somehow
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
    },

    approveRequest: function() {
        splitId = this.parentNode.id.split('-');
        userId = groupId = splitId[splitId.length - 1];
        splitUrl = window.document.URL.split('/');
        groupId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/approve-request/', {'groupId': groupId, 'userId':userId}, Omeka.Groups.requestResponse);
    }
};

jQuery(document).ready(function() {
    jQuery('li.groups-item-add').click(Omeka.Groups.addItemToGroup);
});


