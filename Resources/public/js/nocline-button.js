
/*
 * This file is part of Nocline Bundle.
 *
 * (c) 2012 Bruno ABENA < bruno@activdev.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var _n_output_opened_height = '400px';

function displayNoclineUI(display)
{
    document.getElementById('nocline-d-iframe').style.display = display;
}

function toggleNoclineOutput(force_open)
{
    var e = document.getElementById('nocline-d-w-o-c');
    
    if(force_open !== undefined) 
    {
        if(force_open == true) {
            e.style.height = _n_output_opened_height;
        }
        else {
            e.style.height = '0px';
        }
    }
    else
    {
        if(e.style.height == _n_output_opened_height)
        {
            e.style.height = '0px';
        }
        else
        {
            e.style.height = _n_output_opened_height;
        }
    }
    
    return false;
}

function logNoclineExecResponse(response)
{
    var e = document.getElementById('nocline-d-w-o-c');
    e.innerHTML = response + e.innerHTML;
}

window.onload = function() {
    
    //close output panel
    document.getElementById('nocline-d-w-imc').addEventListener('click', function() {
        toggleNoclineOutput(false);
    }, false);
    
    //close output panel on mouse wheel click
    document.getElementById('nocline-d-w-o').addEventListener('mousedown', function(event) {
        if(event.which == 2)
        {
            toggleNoclineOutput(false);
        }
    }, false);    
}
