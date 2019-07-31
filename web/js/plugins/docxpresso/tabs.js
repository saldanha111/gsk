$(document).ready(function(){
    var numSect = 0;
    if (typeof docxpressoData['settings']['tabs'] !== 'undefined'
        && docxpressoData['settings']['tabs'] == 1) {
        tabbing = 1;
    } else {
        tabbing = 0;
    }
    if (tabbing) {
        var preTabs = $('<div />').html(docxpressoData['settings']['tabNames']).text();
        tabNames = preTabs.split(';');
        numTabs = tabNames.length;
        //check how many sections are there
        numSect = $('#mainDXO section').length;
        counterTabs = numSect - numTabs;
        if (counterTabs > 0) {
            for (var k = 0; k < counterTabs; k++) {
                tabNames.push('Section ' + (numTabs + k +1));
            }  
        }
        //we need to get the section numbers
        sectionNumbers = [];
        $('#mainDXO section').each(function(index){
            idattr = $(this).attr('id');
            idarray = idattr.split('_');
            sectionNumbers.push(idarray[1]);
            //add an order attribute so we can keep trak of the real section number
            $(this).attr('data-order', index);
            $('<div class="visible-xxs accordionTitle"><a data-toggle-tab="tab" data-order="' + index + '" data-tab="' + sectionNumbers[index] + '"  href="#' + tabNames[index] + '"><span class="accordionTab">' + tabNames[index] + '</span></a></div>').insertBefore($(this));
        });
    }
    if (numSect > 0 && numSect < 6 && tabbing) {
        $('<ul class="nav nav-tabs docxnav docxnav-tabs hidden-xxs" id="docxpressoTabBar"></ul>').insertBefore('#mainDXO section:first');
        $('<ul class="nav nav-tabs docxbotnav docxbotnav-tabs hidden-xxs" id="docxpressoBottomTabBar"></ul>').insertAfter('#mainDXO section:last');
        
        for (nt = 0; nt < numSect; nt++) {
            tabnode = '<li data-order="' + nt + '"';
            if (nt == 0) {
                    tabnode += 'class="docxpressoTab active"';
            }
            tabnode += '><a data-toggle-tab="tab" data-order="' + nt + '" data-tab="' + sectionNumbers[nt] + '"  href="#' + tabNames[nt] + '"> ';
            tabnode += tabNames[nt];
            tabnode += '</a></li>';
            $('#docxpressoTabBar').append(tabnode);
            $('#docxpressoBottomTabBar').append(tabnode);
        }
        //show first tab content
        showTabContent(0, 0);
        //add click functionality
        $('a[data-toggle-tab]').click(function(event){
            tabnumber = $(this).attr('data-tab');
            taborder = $(this).attr('data-order');
            
            //hack for full accordion functionality
            if (typeof event.which != 'undefined' && $(this).parent().hasClass("visible-xxs")){
                if ($('#mainDXO #section_' + tabnumber ).css('display') != 'none') {
                    $('#mainDXO #section_' + tabnumber ).css('display','none');
                } else {
                    showTabContent(tabnumber, taborder);
                }
            } else {
                showTabContent(tabnumber, taborder);
            }
            if (typeof event.which != 'undefined'){
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
            return false;
        });
        
        //hack to make the whole accordion collapsible
    } else if (numSect > 5 && tabbing) {
        //insert a mobile type menu
        $('<div class="docxnav hidden-xxs" id="docxpressoTabBar"><button class="btn btn-primary btn-lg"><i class="fa fa-navicon"> </i></button><ul class="mobileMenu" id="mobileTopListMenu"></ul></div>').insertBefore('#mainDXO section:first');
        $('<div class="docxbotnav hidden-xxs" id="docxpressoBottomTabBar"><ul class="mobileMenu" id="mobileBottomListMenu"></ul><button class="btn btn-primary btn-lg"><i class="fa fa-navicon"> </i></button></div>').insertAfter('#mainDXO section:last');
        
        for (nt = 0; nt < numSect; nt++) {
            tabnode = '<li data-order="' + nt + '"';
            if (nt == 0) {
                    tabnode += 'class="docxpressoTab active"';
            }
            tabnode += '><a data-toggle-tab="tab" data-order="' + nt + '" data-tab="' + sectionNumbers[nt] + '"  href="#' + tabNames[nt] + '"> ';
            tabnode += tabNames[nt];
            tabnode += '</a></li>';
            $('#docxpressoTabBar ul').append(tabnode);
            $('#docxpressoBottomTabBar ul').append(tabnode);
        }
        //show first tab content
        showTabContent(0, 0);
        //add click functionality
        $('#docxpressoTabBar button').click(function(){
            $('#mobileTopListMenu').toggle();
        });
        $('#docxpressoBottomTabBar button').click(function(){
            $('#mobileBottomListMenu').toggle();
        });
        $('#mobileTopListMenu').click(function(){
            $('#mobileTopListMenu').show();
        });
        $('#mobileBottomListMenu').click(function(){
            $('#mobileBottomListMenu').show();
        });
        $('a[data-toggle-tab]').click(function(event){
            tabnumber = $(this).attr('data-tab');
            taborder = $(this).attr('data-order');
            $('#mobileTopListMenu').hide();
            $('#mobileBottomListMenu').hide();
            //hack for full accordion functionality
            if (typeof event.which != 'undefined' && $(this).parent().hasClass("visible-xxs")){
                if ($('#mainDXO #section_' + tabnumber ).css('display') != 'none') {
                    $('#mainDXO #section_' + tabnumber ).css('display','none');
                } else {
                    showTabContent(tabnumber, taborder);
                }
            } else {
                showTabContent(tabnumber, taborder);
            }
            if (typeof event.which != 'undefined'){
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            }
            return false;
        });
    }
    //control tab visibility;
    function hideTabContent () {
        $('.h5p_page').each(function(){
            $(this).css('display', 'none');
            $('li[data-order]').removeClass('active');
        });
    }
    function showTabContent (tabnumber, taborder) {
        hideTabContent();
        $('#mainDXO #section_' + tabnumber).css('display', 'table');
        $('li[data-order="' + taborder + '"]').addClass('active');
        width = $('#section_' + tabnumber ).css('width');
        w = width.replace(/px/, '');
        $('#docxpressoTabBar').css('width', (Number(w) + 200) + 'px');
        $('#docxpressoBottomTabBar').css('width', (Number(w) + 200) + 'px');
        //update variable validation
        window.modifiedVisibility = true;
    }
    
    //ckeck if clickTabNum is defined and if so activate that tab
    if (typeof window.clickTabNum != 'undefined') {
        $('li[data-order="' + (clickTabNum -1) + '"] a').click();
        
    }
            
});


