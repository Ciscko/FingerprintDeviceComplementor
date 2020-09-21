<??>
<?php $this->load->view('js-loader');?>
<div class="container-fluid ">
    <div class="row">
    
<div class="container-fluid login">
            <br/>
    <div class="row">
      <div class="col-lg-4">

      </div>
    <div class="col-lg-4">
      <div class="w3-card-8 w3-border w3-black card1  w3-animate-left">
      
        <h3 class="w3-center">Enter Fingerprint ID</h3>
        <hr>
        <form class="w3-margin-16" action="" id="searchform">
          <div class="w3-center form-group">
            <label  for="fingerID"></label>
            <input type="text" id="fingerID" name="fingerID" placeholder="Enter Fingerprint ID" class="form-control"/>
          </div> 
        </form>
          <div  class="w3-center"><button id="searchbtn" class="btn btn-lg btn-primary">SEARCH</button></div><hr>
          
      </div>
    </div>
    <div class="col-lg-4">

    </div>
  </div>
</div>
<div class="modal fade" id="candidatedata">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-body">
        <div class="">
          <div id="photo-preview"></div>
          <hr>
          <div id="detail">
            <b>
              <h2 id="name" class="w3-center">
              </h2>
              <h4 id="regno" class="w3-center">
              </h4>
              <h3 id="printID" class="w3-center">
              </h3>
              <p id="department" class="w3-center">
              </p>
              <h3 id="exam" class="w3-center">
              </h3>
          </b>

          </div>
          <h3 id="error"></h3>
        </div>
          
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="adminlogin">
  <div class="modal-dialog">
    <div class="modal-content ">
      <div class="modal-header">
        <h2 class="w3-center">LOGIN AS ADMIN</h2>
      </div>
      <div class="modal-body w3-center">
      <b id="errorlog"></b>
          <form class="w3-margin-16" action="" id="formLogin">
              <div class="w3-center form-group">
                <label  for="username">USERNAME</label>
                <input type="text" id="username" name="username" placeholder="Enter Username" class="form-control"/>
              </div>
              <div class="w3-center form-group">
                <label  for="pwd">PASSWORD</label>
                <input type="password" id="pwd" name="pwd" placeholder="Enter Password" class="form-control"/>
              </div> 
            </form>
              
      </div>
      <div class="modal-footer">
      <div  class="w3-center"><button id="loginbtn" class="w3-btn w3-blue">LOGIN</button></div>
      </div>
    </div>
  </div>
</div>
<script>
  var base_url = '<?php echo base_url();?>';
  $(document).ready(function(){
    $('#adminbtn').on('click', function(e){
      $('#adminlogin').modal('show');
    });
    $('input[name]').on('keypress', function(){
                $('input[name="pwd"]').css("border","inherit");
                 $('input[name="username"]').css("border","inherit");
                $('#errorlog').empty();
    });
    $('#loginbtn').on('click', function(e){
                e.preventDefault();
                var formData = new FormData($('#formLogin')[0]);
                $.ajax({
                    url:"<?php echo base_url('index.php/User/login');?>",
                    dataType:"JSON",
                    data : formData,
                    contentType:false,
                    processData:false,
                    type:"POST",
                    success : function(datat){
                        if(datat.status){
                            window.location.href = "<?php echo base_url('index.php/Person/candidates')?>"
                        }
                        else{
                            $('input[name="pwd"]').css("border","4px solid red");
                            $('input[name="username"]').css("border","4px solid red");
                            $('#errorlog').text("Invalid Credentials! Please Try Again...").css("color", "red");
                            $('#formLogin')[0].reset();
                        } 
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                            alert(errorThrown+' from server!');
                    }
                });
            });

    $('#searchbtn').on('click', function(e){
        e.preventDefault();
        var finger = $('#fingerID').val();
        if(finger !== ''){
              $.ajax({
                url:"<?php echo base_url('index.php/Person/search_candidate')?>/"+finger,
                type:"GET",
                dataType: "JSON",
                success: function(data){
                        if(data.status){
                           // $("#searchform")[0].reset();
                            $('#error').empty();
                            console.log(data);
                            $('#photo-preview').html('<img src="'+base_url+'upload/'+data.data.photo+'" class="img-responsive">'); // show photo
                            $('#name').text(data.data.firstName+' '+data.data.lastName);
                            $('#regno').text(data.data.regno);
                            $('#printID').html('<b>FingerPrint : <div class="badge badge-lg badge-success">'+data.data.fingerprintID+'</div></b>');
                            $('#department').text(data.data.department+', '+data.data.year);
                            $('#exam').html('<b> Candidature : '+data.data.exam+'</b>');
                            setTimeout(function(){
                              $('#candidatedata').modal('show');
                            }, 1000);
                            
                        }else{
                          $('#photo-preview').empty();
                          $('#name').empty();
                          $('#regno').empty();
                          $('#exam').empty();
                          $('#department').empty();
                           $('#printID').empty();
                          $('#error').html('<b style="color:red;">There is no Such ID or Candidate!</b>');
                          $('#candidatedata').modal('show');
                        }
                },
                error: function(errorThrown, textStatus, jqXHR) {
                    alert(errorThrown);
                }
            }); 
        }
        else{
          $('#error').html('<b style="color:red;">You are entering an empty value! Try again</b>');
          $('#photo-preview').empty();
          $('#name').empty();
          $('#exam').empty();
         $('#department').empty();
          $('#regno').empty();
            $('#printID').empty();
          $('#candidatedata').modal('show');
        }
    });
 });
 
</script>
    </div>
</div>
<?php $this->load->view('footer');?>