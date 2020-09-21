<script src="<?php echo base_url('assets/jquery/jquery-2.1.4.min.js')?>"></script>
<script src="<?php echo base_url('assets/bootstrap/js/bootstrap.min.js')?>"></script>
<script src="<?php echo base_url('assets/datatables/js/jquery.dataTables.min.js')?>"></script>
<script src="<?php echo base_url('assets/datatables/js/dataTables.bootstrap.min.js')?>"></script>
<script src="<?php echo base_url('assets/bootstrap-datepicker/js/bootstrap-datepicker.min.js')?>"></script>
<script>
      $(document).ready(function(){
            $('#admin').on('click', function(){
             checkadmin(); 
           
      });

      function checkadmin()
                {
                        $.ajax({
                            url : "<?php echo site_url('User/check_admin')?>",
                            type: "GET",
                            dataType: "JSON",
                            success: function(data)
                            {
                                //if success 
                                if(data.auth == 'super'){
                                    window.location.href = "<?php echo base_url('User')?>"
                                }
                                else{
                                   // alert('k');
                                    $('#super').modal('show');
                                }
                               
                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                alert('Error communicating.');
                            }
                        });
                
                    
                }
});
     
</script>