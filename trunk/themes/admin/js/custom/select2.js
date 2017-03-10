$(document).ready(function() {
	function formatRepo (repo) {
      if (repo.loading) return repo.text;

      img = document.location.origin  + '/assets/images/uploads/member-no-image.jpg';

      if(repo.profile_photo && repo.profile_photo != 0){
      	if(repo.profile_photo.substr(0,4) == 'http'){
      		img = repo.profile_photo;
      	}
      	else{
      		img = '../../../' + repo.profile_photo.replace('../', '');
      	}
      }
      //console.log(img);
      var markup = "<div class='select2-result-repository clearfix'>" +
      	"<div class='select2-result-repository__avatar'><img src='" + img + "' /></div>" +
        "<div class='select2-result-repository__meta'>" +
          "<div class='select2-result-repository__title'>" + repo.first_name + " " + repo.last_name + "</div>";

      // if (repo.description) {
      //   markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
      // }

      markup += "<div class='select2-result-repository__statistics'>" +
        "<div class='select2-result-repository__forks'><i class='fa fa-envelope'></i> " + repo.email + "</div>" +
        // "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + " Stars</div>" +
        // "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + " Watchers</div>" +
      "</div>" +
      "</div></div>";
      return markup;
    }

    function formatRepoSelection (repo) {
    	     // console.log(repo);
      return repo.first_name  + " " + repo.last_name || repo.email;
    }

  $(".js-example-basic-single").select2({
	ajax: {
	    url: $("#url").val(),
	    dataType: 'json',
	    delay: 250,
	    data: function (params) {
	      return {
	        q: params.term, // search term
	        page: params.page
	      };
	    },
	    processResults: function (data, params) {
	      // parse the results into the format expected by Select2
	      // since we are using custom formatting functions we do not need to
	      // alter the remote JSON data, except to indicate that infinite
	      // scrolling can be used
	      params.page = params.page || 1;
	      // console.log(data, params);
	      return {
	        results: data.items,

	        pagination: {
	          more: (params.page * 30) < data.total_count
	        }
	      };
	    },
	    cache: true
	  },
	  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
	  minimumInputLength: 1,
	  templateResult: formatRepo, // omitted for brevity, see the source of this page
	  templateSelection: formatRepoSelection // omitted for brevity, see the source of this page
	});
});

