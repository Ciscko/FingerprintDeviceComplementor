
    <div class="container ">
        
        </div>
        <div class="container norm w3-card-4 w3-animate-left ">
            <div class="row">
                    <div class="col-lg-12 col-md-12">
                    <h2>ADMINISTRATORS</h2>
            <hr>
            <button class="btn btn-success" onclick="add_user()"><i class="glyphicon glyphicon-plus"></i> Add Admin</button>
            <button class="btn btn-default" onclick="reload_table()"><i class="glyphicon glyphicon-refresh"></i> Reload</button>
            <br />
            <br />
            <table id="users" class="table table-responsive table-bordered table-hover" width="100%">
                <thead class="w3-black">
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Username</th>
                       
                        <th>Auth-Level</th>
                        <th>Phone</th>
                        <th style="width:150px;">Date of Birth</th>
                        <th>Department</th>
                        <th>Photo</th>
                        <th style="width:150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>s
            </table>
                    </div>
            </div>
            <br />
           
        </div>
    
    <?php $this->load->view('js-loader');?>
    
    
    <script type="text/javascript">
    
    var save_method; //for save method string
    var table;
    var base_url = '<?php echo base_url();?>';
    
    $(document).ready(function() {
    var state = false;
   $('#pwdshow').on('click', function(e){
    e.preventDefault();
     state = !state;
    if(state){
        $('#passwordd').attr({'type':'text'});
        $('#pwdshow').text('Hide');
    }
    else{
        $('#passwordd').attr({'type':'password'});
         $('#pwdshow').text('Show');
    }
   });
        //datatables
        table = $('#users').DataTable({ 
    
            "processing": true, //Feature control the processing indicator.
            "serverSide": true, //Feature control DataTables' server-side processing mode.
            "order": [], //Initial no order.
            "scrollX" : true,
                // Load data for the table's content from an Ajax source
            "ajax": {
                "url": "<?php echo site_url('user/ajax_list')?>",
                "type": "POST",
                
            },
    
            //Set column definition initialisation properties.
            "columnDefs": [
                { 
                    "targets": [ -1 ], //last column
                    "orderable": false, //set not orderable
                },
                { 
                    "targets": [ -2 ], //2 last column (photo)
                    "orderable": false, //set not orderable
                },
            ],
    
        });
    
        //datepicker
        $('.datepicker').datepicker({
            autoclose: true,
            format: "yyyy-mm-dd",
            todayHighlight: true,
            orientation: "top auto",
            todayBtn: true,
            todayHighlight: true,  
        });
    
        //set input/textarea/select event when change value, remove class error and remove text help block 
        $("input").change(function(){
            $(this).parent().parent().removeClass('has-error');
            $(this).next().empty();
        });
        $("textarea").change(function(){
            $(this).parent().parent().removeClass('has-error');
            $(this).next().empty();
        });
        $("select").change(function(){
            $(this).parent().parent().removeClass('has-error');
            $(this).next().empty();
        });
    
    });
    function get_photo(photo){
        $('#photoModal').html('<img src="'+base_url+'upload/'+photo+'" class="img-responsive">');
        //$('#adress').html('<div class="well">'+phone+'</div>');
        $('#prevPhoto').modal('show'); 
    }
    
    
    function add_user()
    {
        save_method = 'add';
        $('#user_form')[0].reset(); // reset form on modals
        $('.form-group').removeClass('has-error'); // clear error class
        $('.help-block').empty(); // clear error string
        $('#modal_form').modal('show'); // show bootstrap modal
        $('.modal-title').text('Add User'); // SeUt Title to Botstrap modal title
    
        $('#photo-preview').hide(); // hide photo preview modal
    
        $('#label-photo').text('Upload Photo'); // label photo upload
    }
    
    function edit_user(id)
    {
        save_method = 'update';
        $('#user_form')[0].reset(); // reset form on modals
        $('.form-group').removeClass('has-error'); // clear error class
        $('.help-block').empty(); // clear error string
    
    
        //Ajax Load data from ajax
        $.ajax({
            url : "<?php echo site_url('user/ajax_edit')?>/" + id,
            type: "GET",
            dataType: "JSON",
            success: function(data)
            {
                $('[name="id"]').val(data.id);
                $('[name="email"]').val(data.email);
                $('[name="department"]').val(data.department);
                $('[name="firstName"]').val(data.firstName);
                $('[name="lastName"]').val(data.lastName);
                $('[name="gender"]').val(data.gender);
                $('[name="phone"]').val(data.phone);
                $('[name="username"]').val(data.username);
                $('[name="password"]').val(data.password);
                $('[name="dob"]').datepicker('update',data.dob);
                
                $('#modal_form').modal('show'); // show bootstrap modal when complete loaded
                $('.modal-title').text('Edit User'); // Set title to Bootstrap modal title
    
                $('#photo-preview').show(); // show photo preview modal
    
                if(data.photo)
                {
                    $('#label-photo').text('Change Photo'); // label photo upload
                    $('#photo-preview div').html('<img src="'+base_url+'upload/'+data.photo+'" class="img-responsive thumb">'); // show photo
                    $('#photo-preview div').append('<input type="checkbox" name="remove_photo" value="'+data.photo+'"/> Remove photo when saving'); // remove photo
    
                }
                else
                {
                    $('#label-photo').text('Upload Photo'); // label photo upload
                    $('#photo-preview div').text('(No photo)');
                }
    
    
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert('Error get data from ajax');
            }
        });
    }
    
    function reload_table()
    {
        table.ajax.reload(null,false); //reload datatable ajax 
    }
    
    function save()
    {
        $('#btnSave').text('saving...'); //change button text
        $('#btnSave').attr('disabled',true); //set button disable 
        var url;
    
        if(save_method == 'add') {
            url = "<?php echo site_url('user/ajax_add')?>";
        } else {
            url = "<?php echo site_url('user/ajax_update')?>";
        }
    
        // ajax adding data to database
    
        var formData = new FormData($('#user_form')[0]);
        $.ajax({
            url : url,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "JSON",
            success: function(data)
            {
    
                if(data.status) //if success close modal and reload ajax table
                {
                    $('#modal_form').modal('hide');
                    reload_table();
                }
                else
                {
                    for (var i = 0; i < data.inputerror.length; i++) 
                    {
                        $('[name="'+data.inputerror[i]+'"]').parent().parent().addClass('has-error'); //select parent twice to select div form-group class and add has-error class
                        $('[name="'+data.inputerror[i]+'"]').next().text(data.error_string[i]); //select span help-block class set text error string
                    }
                }
                $('#btnSave').text('save'); //change button text
                $('#btnSave').attr('disabled',false); //set button enable 
    
    
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert('Error adding / update data');
                $('#btnSave').text('save'); //change button text
                $('#btnSave').attr('disabled',false); //set button enable 
    
            }
        });
    }
    
    function delete_user(id)
    {
        if(confirm('Are you sure delete this data?'))
        {
            // ajax delete data to database
            $.ajax({
                url : "<?php echo site_url('user/ajax_delete')?>/"+id,
                type: "POST",
                dataType: "JSON",
                success: function(data)
                {
                    //if success reload ajax table
                    $('#modal_form').modal('hide');
                    reload_table();
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    alert('Error deleting data');
                }
            });
    
        }
    }
    
    </script>
    
    <!-- Bootstrap modal -->
    <div class="modal fade" id="modal_form" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3 class="modal-title">Admin Form</h3>
                </div>
                <div class="modal-body form">
                    <form action="#" id="user_form" class="form-horizontal">
                        <input type="hidden" value="" name="id"/> 
                        <div class="form-body">                          
                            <div class="form-group">
                                <label class="control-label col-md-3">First Name</label>
                                <div class="col-md-9">
                                    <input name="firstName" placeholder="First Name" class="form-control" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Last Name</label>
                                <div class="col-md-9">
                                    <input name="lastName" placeholder="Last Name" class="form-control" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Email</label>
                                <div class="col-md-9">
                                    <input name="email" placeholder="Email." class="form-control" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Username</label>
                                <div class="col-md-9">
                                    <input name="username" placeholder="Username." class="form-control" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Auth Level</label>
                                <div class="col-md-9">
                                    <select name="auth" class="form-control">
                                        <option value="">--Select Auth Level--</option>
                                        <option value="super">Super-Admin</option>
                                         <option value="admin">Admin</option>
                                    </select>
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Password</label>
                                <div class="col-md-9">
                                    <input name="password" id="passwordd" placeholder="password" class="form-control" type="password">
                                   <span class="help-block"></span><button id="pwdshow">Show</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Gender</label>
                                <div class="col-md-9">
                                    <select name="gender" class="form-control">
                                        <option value="">--Select Gender--</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Phone</label>
                                <div class="col-md-9">
                                <input name="phone" placeholder="Phone" class="form-control " type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Date of Birth</label>
                                <div class="col-md-9">
                                    <input name="dob" placeholder="yyyy-mm-dd" class="form-control datepicker" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">Department.</label>
                                <div class="col-md-9">
                                    <input name="department" placeholder="Department." class="form-control" type="text">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group" id="photo-preview">
                                <label class="control-label col-md-3">Photo</label>
                                <div class="col-md-9">
                                    (No photo)
                                    <span class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3" id="label-photo">Upload Photo </label>
                                <div class="col-md-9">
                                    <input name="photo" type="file">
                                    <span class="help-block"></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div class="modal fade" id="prevPhoto">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h3>Passport</h3>
                </div>
                <div class="modal-body">
                    <div id="photoModal">
    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End Bootstrap modal -->
    <?php $this->load->view('footer');?>