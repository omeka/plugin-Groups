if(typeof Omeka === 'undefined') {
    var Omeka = {};
}

Omeka.Groups = {

    clickedElement: null,
        
    toggleGroupSelection: function(e) {
        e.preventDefault();
        var groupCheckbox = jQuery(this).siblings('input[type=checkbox]');
        if (groupCheckbox.is(":checked")) {
            groupCheckbox.removeAttr('checked');
        } else {
            groupCheckbox.prop('checked', true);
        }
        jQuery(this).parent().toggleClass('checked');
    },

    removeItemFromGroup: function(item) {
        var groupId = item.attr('id');
        splitId = groupId.split('-');
        splitUrl = window.location.pathname.split('/');
        groupId = splitId[splitId.length - 1];
        itemId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/remove-item', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.removeItemResponse);
    },
    
    addItemToGroup: function(item) {
        var groupId = item.attr('id');
        splitId = groupId.split('-');
        splitUrl = window.location.pathname.split('/');
        groupId = splitId[splitId.length - 1];
        itemId = splitUrl[splitUrl.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/add-item', {'groupId': groupId, 'itemId':itemId}, Omeka.Groups.addItemResponse);
    },
    
    modifyItemsInGroups: function() {
        jQuery('li.groups-item-add.checked').each(function() {
            Omeka.Groups.addItemToGroup(jQuery(this));
            jQuery(this).removeClass('groups-item-add');
            if (jQuery(this).hasClass('admin')) {
                jQuery(this).addClass('groups-item-exists');
            } else {
                jQuery(this).addClass('groups-item-ineditable');
                jQuery(this).removeClass('checked');
            }
        });
        jQuery('li.groups-item-exists').not('.checked').each(function() {
            Omeka.Groups.removeItemFromGroup(jQuery(this));
            jQuery(this).removeClass('groups-item-exists').addClass('groups-item-add');
        });
        jQuery('.groups-item-add a, .groups-item-exists a').unbind("click");
    },
    
    removeItemResponse: function(responseJson, a, b) {
        if(responseJson.groupId) {
            jQuery('#item-user-group-' + responseJson.groupId).remove();
            jQuery('.empty').removeClass('empty');
            var groupList = jQuery('#item-user-groups');
            if (groupList.children().length < 1) {
                groupList.addClass('empty');
            }
        }
    },

    addItemResponse: function(responseJson, a, b) {
        var groupId = responseJson.groupId;
        if(groupId) {
            li = jQuery('li#groups-id-' + groupId);
            liName = li.find('a').first().text();
            jQuery('#item-user-groups').append("<li id='item-user-group-" + responseJson.groupId + "'><a href='" + Omeka.webRoot + "/groups/show/" + groupId + "'>" + liName + "</a>");
            if (!li.hasClass('admin')) {
                li.html(liName);
            }
            jQuery('.empty').removeClass('empty');
        }
    },

    join: function() {
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/join/' + groupId, {'groupId' : groupId}, Omeka.Groups.joinResponse);
    },

    joinResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        //reloading to show change in membership
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
        Omeka.Groups.clickedElement = this;
        splitId = this.id.split('-');
        groupId = splitId[splitId.length - 1];
        jQuery.post(Omeka.webRoot + '/groups/request/' + groupId, null, Omeka.Groups.requestResponse);
    },

    requestResponse: function(response, a, b) {
        var responseJson = JSON.parse(response);
        if(responseJson.status === 'ok') {
            html = "<p class='groups-pending'>Membership request is pending</p>";
            jQuery(Omeka.Groups.clickedElement).replaceWith(html);
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



(function($) {
    $(document).ready(function() {
        $('span.groups-item-remove').click(Omeka.Groups.removeItemFromGroup);
        $('ul#groups-group-list li').click(Omeka.Groups.filterGroups);
        $('input.groups-invitation-action').click(Omeka.Groups.toggleSecondaryAdminOptions);
        $('input.groups-membership-options').click(Omeka.Groups.toggleSecondaryAdminOptions);
        $('#groups-commenting-copy').click(Omeka.Groups.copyComment);
        Omeka.Groups.wysiwyg();
        Omeka.Groups.wysiwyg({elements: 'groups_commenting_body', width: "100%"});
        $('#groups-commenting > label').click(function() {
            $('#groups_comment_form').toggle('slow');    
        });
        $('#groups_comment_form').hide();
        $('p.groups-join-button').click(Omeka.Groups.join);
        $('.visibility').after('<span class="more-info">?</span>');

        if ($('body').hasClass('items') && $('body').hasClass('show')) {
            $('.launch-add-item').modal({
                trigger: '.launch-add-item',
                olay:'div.overlay',             // id or class of overlay
                animationSpeed: 400,            // speed of overlay in milliseconds | default=400
                moveModalSpeed: 'slow',         // speed of modal movement when window is resized | slow or fast | default=false
                background: '000000',           // hexidecimal color code - DONT USE #
                opacity: 0.5,                   // opacity of modal |  0 - 1 | default = 0.8
                openOnLoad: false,              // open modal on page load | true or false | default=false
                docClose: false,                 // click document to close | true or false | default=true    
                closeByEscape: true,            // close modal by escape key | true or false | default=true
                moveOnScroll: true,             // move modal when window is scrolled | true or false | default=false
                resizeWindow: true,             // move modal when window is resized | true or false | default=false
                close:'.close-button'               // id or class of close button
            });
            
            var currentStateClone = '';
            $('.add-to-groups').bind("click", Omeka.Groups.modifyItemsInGroups);
            
            $('.launch-add-item').on('click', function() {
                currentStateClone = $('#user-groups').clone();
                $('.groups-item-add a, .groups-item-exists a').bind("click", Omeka.Groups.toggleGroupSelection);
            });
            
            $('.modal-header .close-button').on('click', function() {
                $('#user-groups').replaceWith(currentStateClone);
                $('.groups-item-add a, .groups-item-exists a').unbind("click");
            });
        }
    });
})(jQuery)
