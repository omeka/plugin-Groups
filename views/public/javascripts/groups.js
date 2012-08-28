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
        var splitId = this.parentNode.id.split('-');
        var userId = groupId = splitId[splitId.length - 1];
        var splitUrl = window.document.URL.split('/');
        var groupId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/approve-request/', {'groupId': groupId, 'userId':userId}, Omeka.Groups.approveRequestResponse);        
        
    },

    approveRequestResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);        
        window.location.reload(true);
    },
    
    copyComment: function() {
        var body = tinyMCE.get('commenting_body').getContent();
        tinyMCE.get('groups_commenting_body').setContent(body);
    },
    
    toggleSecondaryAdminOptions: function() {
        parent = jQuery(this).parent(); 
        val = parent.children('input:checked').val();   
        switch(val) {
            case 'decline':
            case 'deny':
            case 'remove':
                parent.children('div.groups-block-entities').show('fast');
                parent.children('div.pending').hide('fast');
                break;
                
            case 'approve':
                parent.children('div.pending').show('fast');
                parent.find('div.groups-block-entities input').removeAttr('checked');
                parent.children('div.groups-block-entities').hide('slow');                
                break;
            default:
                
                parent.find('div.groups-block-entities input').removeAttr('checked');
                parent.children('div.groups-block-entities').hide('slow');            
                parent.children('div.pending').hide('slow');
        }
    }    
};


/**
 * Add the TinyMCE WYSIWYG editor to a page.
 * Default is to add to all textareas.
 * Modified from the admin-side global.js Omeka.wysiwyg
 *
 * @param {Object} [params] Parameters to pass to TinyMCE, these override the
 * defaults.
 */
Omeka.Groups.wysiwyg = function (params) {
    // Default parameters
    initParams = {
        plugins: "paste,inlinepopups",
        convert_urls: false,
        mode: "exact", 
        elements: 'groups_description',
        object_resizing: true,
        theme: "advanced",
        theme_advanced_toolbar_location: "top",
        force_br_newlines: false,
        forced_root_block: 'p', // Needed for 3.x
        remove_linebreaks: true,
        fix_content_duplication: false,
        fix_list_elements: true,
        valid_child_elements: "ul[li],ol[li]",
        theme_advanced_buttons1: "bold,italic,underline,link",
        theme_advanced_buttons2: "",
        theme_advanced_buttons3: "",
        theme_advanced_toolbar_align: "left"
    };

    // Overwrite default params with user-passed ones.
    for (var attribute in params) {
        // Account for annoying scripts that mess with prototypes.
        if (params.hasOwnProperty(attribute)) {
            initParams[attribute] = params[attribute];
        }
    }

    tinyMCE.init(initParams);
};




jQuery(document).ready(function() {
    jQuery('li.groups-item-add').click(Omeka.Groups.addItemToGroup);
    jQuery('ul#groups-group-list li').click(Omeka.Groups.filterGroups);
    jQuery('input.groups-invitation-action').click(Omeka.Groups.toggleSecondaryAdminOptions);
    jQuery('input.groups-membership-options').click(Omeka.Groups.toggleSecondaryAdminOptions);
    jQuery('#groups-commenting-copy').click(Omeka.Groups.copyComment);
    Omeka.Groups.wysiwyg();
    Omeka.Groups.wysiwyg({elements: 'groups_commenting_body', width: "100%"});
    jQuery('#groups-commenting > label').click(function() {
        jQuery('#groups_comment_form').toggle('slow');    
    });
    jQuery('#groups_comment_form').hide();
});


