
/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var _n_is_last_cmd_enabled   = true;
var _n_if_always_show_output = false;
var _n_nb_last_commands      = 2;

var _n_response_is_error  = false;
var _n_last_command_html  = '';
var _n_last_command_title = '';
var _n_top_n_command_tpl  = '\n\
<li><span id="nocline-top-span">Last used commands</span>\n\
<ul id="nocline-ul-top-cmd"><li>%a</li></ul>\n\
</li>';

jQuery(function() 
{
    miscInit();
    callForm();
    submitForm();
    addRemoveMultipleinput();
});

function miscInit()
{
    
    //ajax indicator
    jQuery(document).ajaxStart(function()
    {
        jQuery('#nocline-mw-r-a').show();
    }).ajaxStop(function() 
    {
        jQuery('#nocline-mw-r-a').hide();
    });
    
    //toggle output panel, reload commands list
    $('#nocline-w-op').on('click', function() {parent.toggleNoclineOutput();});
    $('#nocline-w-or').on('click', function() {location.reload();});
    
    //show / hide main window
    jQuery('#nocline-m-btn').on('click', function()
    {
        jQuery(this).hide();
        jQuery('#nocline-mw').css({display:'block', opacity:'1'});
        jQuery('#nocline-mw-f-t').focus();
    });
    
    //filter commands
    jQuery('#nocline-mw-f-t').keyup(function(event)
    {
        var input = jQuery.trim(jQuery(this).val());
        var txt, reg;

        if(input == '')
        {
            jQuery('#nocline-mw-ll li').show();
            return false;
        }

        jQuery('#nocline-mw-ll li').each(function (){
            try{
                reg = new RegExp(input, "i");
            }
            catch(e){
                return false;
            }

            txt  = jQuery(this).text();
            pTxt = jQuery(this).parents('li').find('span').text();

            if(reg.test(txt) === false && reg.test(pTxt) === false) 
            {
                jQuery(this).hide();
            }
            else
            {
                jQuery(this).show();
            }
        });

    });
    
    //remove error classes on inputs
    jQuery('#nocline-mw-r-b form .error').live('keyup', function()
    {
        if(jQuery.trim(jQuery(this).val()))
        {
            jQuery(this).removeClass('error');
        }
    });
    
    //hide command form
    jQuery('#nocline-mw-r-c').on('click', function() 
    {
        jQuery('#nocline-mw').css('width', 'auto');
        jQuery('#nocline-mw-r').hide();        
    });
    
    //hide commands list
    jQuery('#nocline-mw-sc').on('click', function() 
    {
        jQuery(this).css('opacity', 0.3);
        window.parent.displayNoclineUI('none');
    });
}

function callForm()
{
    jQuery('#nocline-mw-ll a').live('click', function(event) 
    {
        event.stopPropagation();
        
        var $aTag = jQuery(this);
        
        //take the last command html value
        _n_last_command_title = jQuery.trim($aTag.attr('data-title'));
        _n_last_command_html  = $aTag.parent().html().replace(/">.*<\/a>/, '">'+$aTag.attr('data-title')+'</a>');
        
        //resize nocline window, show form wrapper
        jQuery('#nocline-mw').css('width', '400px');
        jQuery('#nocline-mw-r').show();
        jQuery('#nocline-mw-r-t').text($aTag.attr('data-title'));
        
        jQuery.ajax(
        {
            url: $aTag.attr('href'),
            dataType: 'json',
            success: function(data)
            {
                jQuery('#nocline-mw-r-b').html(data.html);
                
                //set auto focus on the first field
                jQuery('#command div input, #command div select').first().focus();
                
                //hide duplicated checkboxes labels
                jQuery('.label_chk').siblings().not('.label_chk').hide();                
                
                //if the config always show output is enabled, check the checkbox
                if(_n_if_always_show_output)
                {
                    jQuery('#nocline-mw-r-oo').attr('checked', 'checked');
                }
            }
        });
        
        return false;
    });
}

function addRemoveMultipleinput()
{
    //remove input
    jQuery('body').delegate('#nocline-mw-r-b span.nocline-span-del', 'click', function(event) 
    {
        jQuery(this).prev().remove();
        jQuery(this).remove();
    });
    
    //add input
    jQuery('body').delegate('#nocline-mw-r-b span.nocline-span-add', 'click', function(event) 
    {
        var $parent = jQuery(this).parent();
        var $input  = jQuery(this).prev();
        
        jQuery('<span class="nocline-span-del"><span>-</span></span>'+
               '<input class="nocline-text-add" type="text" name="'+$input.attr('name')+'">').appendTo($parent);
    });
}

function submitForm()
{
    jQuery('#nocline-mw-btn-s').on('click', function(event) 
    {
        if(!validateForm())
        {
            return false;
        }
        
        var $form = jQuery('#nocline-mw-r-b form');
        
        //insert command in the last used html list
        insertInLastUsedList();
        
        jQuery.ajax({
            url   : $form.attr('action'),
            data  : $form.serializeArray(),
            type  : 'POST',
            error : function (jqXHR, textStatus, errorThrown)
            {
                parent.logNoclineExecResponse(createLog(
                    '<pre>'+jqXHR.status+' '+jqXHR.statusText+': see your browser error log console for more details.</pre>', true)
                    //'<pre>'+jqXHR.status+' '+jqXHR.statusText+'</pre>' + '<div>'+jqXHR.responseText+'</div>'+'<pre></pre>', true)
                );
                tryToOpenOutput(true);
            },
            success : function (data, textStatus, jqXHR)
            {
                _n_response_is_error = /(<!-- 1 -->)/.test(data);
                parent.logNoclineExecResponse(createLog(data, _n_response_is_error));
                tryToOpenOutput(_n_response_is_error, (jqXHR.status != 200));
            }
        });
    });
}

function createLog(data, is_error)
{
    return is_error ? data.replace('<pre>', '<pre class="nocline-d-w-o-c-ko">') : data;
}

function tryToOpenOutput(is_error, bad_status)
{
    if(is_error || jQuery('#nocline-mw-r-oo').is(':checked') || bad_status)
    {
        parent.toggleNoclineOutput(true);
    }
}

function validateForm()
{
    var isOk = true;
    jQuery('#nocline-mw-r-b form *[required="required"]').each(function()
    {
        if(!jQuery.trim(jQuery(this).val()))
        {
            jQuery(this).addClass('error');
            isOk = false;
        }
    });
    
    return isOk;
}

function insertInLastUsedList()
{
    if(!_n_is_last_cmd_enabled)
    {
        return;
    }
    
    if(_n_last_command_html != '')
    {
        if(jQuery('#nocline-ul-top-cmd').length > 0)
        {
            //if the command is already added, skip
            var is_already_added = false;
            jQuery('#nocline-ul-top-cmd li').each(function(){
                if(jQuery.trim(jQuery(this).text()) == _n_last_command_title)
                {
                    is_already_added = true;
                    return false;
                }
            });
            if(is_already_added)
            {
                return;
            }
        
            //add to list
            jQuery('#nocline-ul-top-cmd').prepend('<li>'+_n_last_command_html+'</li>');
            
            //remove last one if nb max configured is reached
            if(jQuery('#nocline-ul-top-cmd li').length > _n_nb_last_commands)
            {
                jQuery('#nocline-ul-top-cmd li:last').remove();
            }
        }
        else // create list last used from template
        {
            jQuery('#nocline-mw-ll').prepend(_n_top_n_command_tpl.replace('%a', _n_last_command_html));
        }
    }
    
    _n_last_command_html = '';
}