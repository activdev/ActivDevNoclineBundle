<script type="text/javascript">
    
    /*
    * This file is part of Nocline Bundle.
    *
    * (c) 2012 Bruno ABENA < bruno@activdev.com >
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
    */

    $(function()
    {
        $('#command_--namespace').live('keyup', function()
        {
            $('#command_--bundle-name').val($(this).val().replace(/\/|\\/, ''));
        });        
    });
    
</script>