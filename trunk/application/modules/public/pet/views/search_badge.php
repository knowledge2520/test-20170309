<!-- SUBSCRIBE -->
<div class="highlight-bg has-margin-bottom" style="background-color:#FFFFFF;"> 
  <!-- <div class="container">
    <div class="row">
        <div class="form-group col-sm-4 col-md-3 col-xs-12"  style="text-align:center;">
       <img src="images/badge_search.png" style="max-height:120px;">
        </div>
        <div class="form-group col-sm-6 col-md-7 col-xs-12" style="padding-top:30px;text-align:center;">
        
          <h4 style="font-size:28px;">I found a pet with a badge. What should I do?</h4>
          </div>
          <div class="form-group col-sm-2 col-md-2 col-xs-12" style="padding-top:30px;text-align:center;"> <a class="btn btn-lg btn-info" href="#" role="button">Details →</a></div>

    </div>
  </div>--> 
</div>
<!-- END SUBSCRIBE --> 

<!--BADGE BLOCK-->
<div class="container">
  <div class="row feature-block has-margin-bottom">
    <div class="col-md-1 col-sm-12"></div>
    <div class="col-md-5 col-sm-12 has-margin-bottom" style="text-align:center;"> <img class="img-responsive" src="<?php echo base_url('themes/pet/img/search_badge.jpg');?>" alt="Search badge ID"> </div>
    <div class="col-md-5 col-sm-12 has-margin-bottom" style="text-align:center;font-size:25px;">Found a Pet?<br />
      Enter <span class="petbadgesearch">badge</span> ID below</div>
    <div class="col-md-5 col-sm-12 has-margin-bottom" >
      <form action="<?php echo site_url('search-badge');?>" method="post" id="mc-embedded-subscribe-form">
        <div class="form-group col-sm-8 col-md-8">
          <label class="sr-only">Email address</label>
          <input type="text" name="badgeId" class="form-control input-lg" id="mce-EMAIL" >
          <?php if(isset($error) && !empty($error)): ?>
          <div class="alert alert-danger" role="alert" style="margin-top:5px;">Invalid <span class="petbadgesearch">badge</span> ID. Please enter a valid <span class="petbadgesearch">badge</span> ID and try again.</div>
          <?php endif;?>
          <span class="help-block" id="result"></span> </div>
        <div class="form-group col-sm-4 col-md-4">
          <button type="submit" name="subscribe" class="btn btn-lg btn-info btn-block">SEARCH</button>
        </div>
      </form>
    </div>
    <div class="col-md-1 col-sm-12"></div>
  </div>
</div>
<!-- // END BADGE BLOCK--> 