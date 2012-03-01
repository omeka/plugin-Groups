if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    addItemToGroup: function() {

        splitId = this.id.split('-');
        splitUrl = window.document.URL.split('/');
        groupId = splitId[splitId.length - 1];
        itemId = splitUrl[splitUrl.length - 1];
        jQuery.post('/commons/groups/add-item-to-group', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.addItemResponse);
    },

    addItemResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        //notify on the item somehow
    }

};

jQuery(document).ready(function() {
    jQuery('li.groups-item-add').click(Omeka.Groups.addItemToGroup);
});


