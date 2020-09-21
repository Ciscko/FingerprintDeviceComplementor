<!DOCTYPE html>
<html>
    <head> 
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fingerprint-Based Authentication</title>
    <link rel="icon" href="<?php echo base_url().'assets/logo2.png';?>">
    <link href="<?php echo base_url('assets/w3.css')?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css')?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/datatables/css/dataTables.bootstrap.min.css')?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')?>" rel="stylesheet">
    <link href="<?php echo base_url('assets/custom.css')?>" rel="stylesheet">
   
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    </head> 
<body>

<section>
      <ul class="w3-navbar w3-large w3-purple">
      
      <?php  if(!$islogged){ ?>
            <li class=""><a id="adminbtn" class="w3-hover-black w3-text-white">STUDENTS</a></li>
      <?php } if($islogged){ ?>
            <li class="w3-right"><a class="w3-hover-black w3-text-orange" href="<?php echo base_url('User/logout')?>">LOGOUT</a></li>
      <?php  if($module == 'candidate') { ?>
            <li class="w3-right"><a  id="admin" class="w3-hover-black w3-text-white">SUPER_ADMIN</a></li>
      <?php } if($module == 'user') {?>
            <li class=""><a href="<?php echo base_url('Person/candidates')?>" class="w3-hover-black w3-text-white">STUDENTS</a></li>
      
           <?php }  }?>
      </ul> 
</section>
<div class="modal fade" id="super">
      <div class="modal-dialog">
            <div class="modal-content ">
               <div class="modal-body w3-center">
                        <b class="errorlog">YOU ARE NOT A SUPER ADMIN !</b>     
               </div>
            </div>
      </div>
</div>