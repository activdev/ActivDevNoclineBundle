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
        $('#command_--entity').live('change', function()
        {
            $('#command_--route-prefix').val(_.string.underscored($(this).val().replace('Bundle', '').replace(':', '_')));
        });
    });    
</script>