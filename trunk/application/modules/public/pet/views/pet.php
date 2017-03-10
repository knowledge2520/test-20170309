<!-- SUBSCRIBE -->
<div class="highlight-bg has-margin-bottom"
	style="background-color: #FFFFFF;">
	<!-- <div class="container">
    <div class="row">
        <div class="form-group col-sm-4 col-md-3 col-xs-12"  style="text-align:center;">
       <img src="images/badge_search.png" style="max-height:120px;">
        </div>
        <div class="form-group col-sm-6 col-md-7 col-xs-12" style="padding-top:30px;text-align:center;">
        
          <h4 style="font-size:28px;">I found a pet witd a badge. What should I do?</h4>
          </div>
          <div class="form-group col-sm-2 col-md-2 col-xs-12" style="padding-top:30px;text-align:center;"> <a class="btn btn-lg btn-info" href="#" role="button">Details →</a></div>

    </div>
  </div>-->
</div>
<!-- END SUBSCRIBE -->

<!--BADGE BLOCK-->
<div class="container main-font main-wrapper">

    <!-- Responsive logo for mobile-->
    <div class="item-header-logo-mobile">
        <a href="<?php echo site_url();?>" title="Pet Widget" rel="home"><img src="<?php echo base_url('themes/pet/img/badge_white_bg.png');?>" alt="Logo"></a>
    </div>

    <!-- If no logo, display site title and description -->

	<div class="item-header row" role="complementary">
        <div class="item-header-avatar">
          <a href="<?php echo $pet->code?>">
              <img src="<?php echo checkMediaExist($pet->profile_photo) ? $pet->profile_photo : site_url('themes/public/images/placeholder.png')?>" class="img-responsive" width="150" height="150" alt="Profile picture"/>
          </a>
        </div><!-- #item-header-avatar -->
        <div class="item-header-content">
            <div class="entry-title">
               <?php echo $pet->name?></div>
            <div class="user-nicename">Owner: <?php echo $pet->first_name . ' ' . $pet->last_name;?></div>
            <span class="activity">Last updated: <?php echo $pet->modified_date;?></span>
        </div><!-- #item-header-content -->
        <div class="item-header-logo">
            <a href="<?php echo site_url();?>" title="Pet Widget" rel="home"><img src="<?php echo base_url('themes/pet/img/badge_white_bg.png');?>" alt="Logo"></a>
        </div>
        <div id="item-buttons" class="profile">
        </div><!-- #item-buttons -->
    </div><!-- #item-header -->
    <div class="row">
    
	<h4><strong>General</strong></h4>
	<table class="table table-striped table-bordered table-fixed-layout">
		<tbody>
			<tr>
				<td class="item-label"><strong>Name</strong></td>
				<td class="item-data"><?php echo $pet->name;?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Date of Birth</strong></td>
				<td class="item-data"><?php echo isset($pet->dob) && $pet->dob != 0 ? $pet->dob : '' ;?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Age</strong></td>
				<td class="item-data"><?php echo isset($pet->dob_years_old) && !empty($pet->dob_years_old) ? $pet->dob_years_old : '';?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Type</strong></td>
				<td class="item-data"><?php echo $pet->pet_type ? $pet->pet_type : '';?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Breed</strong></td>
				<td class="item-data"><?php echo $pet->breed ? $pet->breed : '';?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Sex</td>
				<td class="item-data"><?php echo $pet->sex == 0 ? 'Male' : 'Female';?>
				</td>
			</tr>
			<tr>
				<td class="item-label"><strong>Color</td>
				<td class="item-data"><?php echo $pet->color;?></td>
			</tr>
			<tr>
				<td class="item-label"><strong>Microchip Number</strong></td>
				<td class="item-data"><?php echo $pet->microchip;?></td>
			</tr>
		</tbody>
	</table>
	
	<?php if($pet_settings && ($pet_settings->contact_name || $pet_settings->contact_primary_number || $pet_settings->contact_alternate_number_1 || $pet_settings->contact_alternate_number_2 || $pet_settings->contact_email)):?>
		<h4><strong>Contact</strong></h4>
		<table class="table table-striped table-bordered table-fixed-layout">
			<tbody>
		    <?php if($pet_settings && $pet_settings->contact_name):?>
		        <tr>
					<td class="item-label"><strong>Name</strong></td>
					<td class="item-data"><?php echo $contact && !empty($contact->name) ? $contact->name : '';?>
					</td>
				</tr>
		     <?php endif;?>
		     <?php if($pet_settings && $pet_settings->contact_primary_number):?>
		        <tr>
					<td class="item-label"><strong>Primary Number</strong></td>
					<td class="item-data"><?php echo $contact && !empty($contact->phone) ? '<a href="tel:'.$contact->phone.'">'.$contact->phone.'</a>' : '';?>
					</td>
				</tr>
		     <?php endif;?>
		     <?php if($pet_settings && $pet_settings->contact_alternate_number_1):?>
		        <tr>
					<td class="item-label"><strong>Alternate Number 1</strong></td>
					<td class="item-data"><?php echo $contact && !empty($contact->alternate_phone_1) ? '<a href="tel:'.$contact->alternate_phone_1.'">'.$contact->alternate_phone_1.'</a>' : '';?>
					</td>
				</tr>
		     <?php endif;?>
		     <?php if($pet_settings && $pet_settings->contact_alternate_number_2):?>
		        <tr>
					<td class="item-label"><strong>Alternate Number 2</strong></td>
					<td class="item-data"><?php echo $contact && !empty($contact->alternate_phone_2) ? '<a href="tel:'.$contact->alternate_phone_2.'">'.$contact->alternate_phone_2.'</a>' : '';?>
					</td>
				</tr>
		     <?php endif;?>
		     <?php if($pet_settings && $pet_settings->contact_email):?>
		        <tr>
					<td class="item-label"><strong>Email</strong></td>
					<td class="item-data"><?php echo $contact && !empty($contact->email) ? '<a href="mailto:'.$contact->email.'" target="_top">'.$contact->email.'</a>' : '';?>
					</td>
				</tr>
		     <?php endif;?>
		     </tbody>
	     </table>
     <?php endif;?>
     
     <?php if($pet_settings && $pet_settings->veterinarian):?>
		<h4><strong>Veterinarian</strong></h4>
		<table class="table table-striped table-bordered table-fixed-layout">
			<tbody>
				<tr>
					<td class="item-label"><strong>Clinic</strong></td>
					<td class="item-data"><?php echo $veterinarian && !empty($veterinarian->clinic) ? $veterinarian->clinic : '';?>
					</td>
				</tr>
				<tr>
					<td class="item-label"><strong>Doctor</strong></td>
					<td class="item-data"><?php echo $veterinarian && !empty($veterinarian->doctor) ? $veterinarian->doctor : '';?>
					</td>
				</tr>
				<tr>
					<td class="item-label"><strong>Phone Number</strong></td>
					<td class="item-data"><?php echo $veterinarian && !empty($veterinarian->phone) ? '<a href="tel:'.$veterinarian->phone.'">'.$veterinarian->phone.'</a>' : '';?>
					</td>
				</tr>
				<tr>
					<td class="item-label"><strong>Address</strong></td>
					<td class="item-data"><?php echo $veterinarian && !empty($veterinarian->address) ? '<a href="http://maps.google.com/?q='.$veterinarian->address.'" target="_blank">'.$veterinarian->address.'</a>' : '';?>
					</td>
				</tr>
			</tbody>			
		</table>
    <?php endif;?>
    
    <?php if($pet_settings && $pet_settings->medications):?>
    	<?php if(isset($medications) && !empty($medications)):?>
			<h4><strong>Medications</strong></h4>
            <?php foreach ($medications as $medication):?>
            	<table class="table table-striped table-bordered table-fixed-layout">
	            	<tbody>
		            	<tr>
							<td class="item-label"><strong>Medication</strong></td>
							<td class="item-data"><?php echo $medication->name ? $medication->name : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Purpose</strong></td>
							<td class="item-data"><?php echo $medication->purpose ? $medication->purpose : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Notes</strong></td>
							<td class="item-data"><?php echo $medication->notes ? $medication->notes : '';?>
							</td>
						</tr>
						<?php	
				          if (! empty ( $medication->reminder_times )) {
				          	$reminder_times = json_decode ( $medication->reminder_times );
				          } else {
				          	$reminder_times = '';
				          }
				          ?>
				  		<tr>
							<td class="item-label"><strong>Daily Frequency</strong></td>
							<td class="item-data"><?php echo !empty($reminder_times) && $reminder_times->reminder_times ? sizeof($reminder_times->reminder_times) : ''?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Time & Dosage</strong></td>
							<td class="item-data">
								<?php
								if (! empty ( $reminder_times ) && $reminder_times->reminder_times) {
									$times = $reminder_times->reminder_times;
									if (is_array ( $times ) && sizeof ( $times ) > 0) {
										foreach ( $times as $k => $t ) {
											$dosage = $t->quantity > 1 ? $t->quantity . ' doses' : $t->quantity . ' dose';
											if ($k == sizeof ( $times ) - 1) {
												echo date ( "h:i a", strtotime ( $t->times ) ) . ' - ' . $dosage;
											} else {
												echo date ( "h:i a", strtotime ( $t->times ) ) . ' - ' . $dosage . '<br/>';
											}
										}
									}
								}
								?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Start date</strong></td>
							<td class="item-data"><?php echo $medication->reminder_start_date ? date('d F Y', $medication->reminder_start_date) : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Duration</strong></td>
							<td class="item-data">
								
							    <?php
									if (! empty ( $reminder_times ) && $medication->reminder_duration !== FALSE) {
										switch ($medication->reminder_duration) {
											case 0 :
												echo 'Continuous';
												break;
											case 1 :
												echo '1 day';
												break;
											default :
												echo $medication->reminder_duration . ' days';
												break;
										}
									}
								?>
			                    
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Days</strong></td>
							<td class="item-data">
								
			                    <?php
								if (! empty ( $reminder_times ))
									echo ! $medication->reminder_days ? 'Mon, Tue, Wed, Thu, Fri, Sat, Sun' : $medication->reminder_days;
								else
									echo '';
								?>
			                    
			
							</td>
						</tr>
	            	</tbody>
				</table>
        	<?php endforeach;?>
       <?php else:?>  
		<h4><strong>Medications</strong></h4>
		<table class="table table-striped table-bordered table-fixed-layout">
			<tbody>
				<tr>
					<td class="item-label"><strong>Medication</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Purpose</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Notes</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Daily Frequency</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Time & Dosage</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Start date</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Duration</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Days</strong></td>
					<td class="item-data"></td>
				</tr>
			</tbody>
		</table> 
      <?php endif;?>
    <?php endif;?>
     
	<?php if($pet_settings && $pet_settings->allergies):?>
    	<?php if(isset($allergies) && !empty($allergies)):?>
			<h4><strong>Allergies</strong></h4>
            <?php foreach ($allergies as $allergy):?>
            	<table class="table table-striped table-bordered table-fixed-layout">
	            	<tbody>
		            	<tr>
							<td class="item-label"><strong>Allergy</strong></td>
							<td class="item-data"><?php echo $allergy->name ? $allergy->name : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Reaction</strong></td>
							<td class="item-data"><?php echo $allergy->reaction ? $allergy->reaction : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Remedy</strong></td>
							<td class="item-data"><?php echo $allergy->remedy ? $allergy->remedy : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Last Updated</strong></td>
							<td class="item-data"><?php echo $allergy->last_update ?  date('d F Y', $allergy->last_update) : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Notes</strong></td>
							<td class="item-data"><?php echo $allergy->notes ? $allergy->notes : '';?>
							</td>
						</tr>
	            	</tbody>
				</table>
  			<?php endforeach;?>
     	<?php else:?>
		<h4><strong>Allergies</strong></h4>
		<table class="table table-striped table-bordered table-fixed-layout">
			<tbody>
				<tr>
					<td class="item-label"><strong>Allergy</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Reaction</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Remedy</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Last Updated</strong></td>
					<td class="item-data"></td>
				</tr>
				<tr>
					<td class="item-label"><strong>Notes</strong></td>
					<td class="item-data"></td>
				</tr>
			</tbody>
		</table>
      <?php endif;?>
	<?php endif;?>
	
	<?php if($pet_settings && $pet_settings->vaccinations):?>
		<?php if(isset($vaccinations) && !empty($vaccinations)):?>
			<h4><strong>Vaccinations</strong></h4>
	        <?php foreach ($vaccinations as $vaccination):?>
            	<table class="table table-striped table-bordered table-fixed-layout">
	            	<tbody>
		            	<tr>
							<td class="item-label"><strong>Vaccination</strong></td>
							<td class="item-data"><?php echo $vaccination->name ? $vaccination->name : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Date</strong></td>
							<td class="item-data"><?php echo $vaccination->use_date ?  date('d F Y', $vaccination->use_date) : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Clinic</strong></td>
							<td class="item-data"><?php echo $vaccination->clinic ? $vaccination->clinic : '';?>
							</td>
						</tr>
						<tr>
							<td class="item-label"><strong>Notes</strong></td>
							<td class="item-data"><?php echo $vaccination->notes ? $vaccination->notes : '';?>
							</td>
						</tr>
	            	</tbody>
				</table>
         	<?php endforeach;?>
		<?php else:?>
			<h4><strong>Vaccinations</strong></h4>
			<table class="table table-striped table-bordered table-fixed-layout">
				<tbody>
					<tr>
						<td class="item-label"><strong>Vaccination</strong></td>
						<td class="item-data"></td>
					</tr>
					<tr>
						<td class="item-label"><strong>Date</strong></td>
						<td class="item-data"></td>
					</tr>
					<tr>
						<td class="item-label"><strong>Clinic</strong></td>
						<td class="item-data"></td>
					</tr>
					<tr>
						<td class="item-label"><strong>Notes</strong></td>
						<td class="item-data"></td>
					</tr>
				</tbody>
			</table>
        <?php endif;?>
	<?php endif;?>
                                                        
	<?php if($pet_settings && $pet_settings->reward && !empty($pet_settings->reward)): ?>
    	<?php
			$reward = json_decode ( $pet_settings->reward );
			if (! empty ( $reward ) && $reward->reward_check) :
		?>
			<h4><strong>Reward Offered!</strong></h4>
			<table class="table">
				<tbody>
					<tr>
						<td class="item-data"><?php echo $reward->reward_currency . ' ' . $reward->reward_value;?></td>
					</tr>
				</tbody>
			</table>
        <?php endif;?>
    <?php endif;?>
                                                        
    <?php if($pet_settings && $pet_settings->notes_check):?>
		<h4><strong>Notes</strong></h4>
		<table class="table">
			<tbody>
				<tr>
					<td class="item-data"><?php echo $pet_settings->notes ? $pet_settings->notes : "&nbsp;"?></td>
				</tr>
			</tbody>			
		</table>
    <?php endif;?>
    </div>
</div>
<!-- // END BADGE BLOCK-->
