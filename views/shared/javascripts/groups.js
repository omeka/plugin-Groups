if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    addItemToGroup: function() {
        var id = this.id;
        var groupId  = id.substr(-1, 1);
        var itemId = window.document.URL.substr(-1, 1);
        jQuery.post('/commons/groups/group/add-item-to-group', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.addItemResponse);


    },

    addItemResponse: function(response) {
        alert(response);
    }

};

jQuery(document).ready(function() {
    jQuery('li.groups-item-add').click(Omeka.Groups.addItemToGroup);
});


