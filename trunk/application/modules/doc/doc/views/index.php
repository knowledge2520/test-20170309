<!DOCTYPE html>
<html>
<head>
    <title>PetWidget API document</title>
    <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16" />
    <link href='<?php echo base_url();?>swagger/css/typography.css' media='screen' rel='stylesheet' type='text/css'/>
    <link href='<?php echo base_url();?>swagger/css/reset.css' media='screen' rel='stylesheet' type='text/css'/>
    <link href='<?php echo base_url();?>swagger/css/screen.css' media='screen' rel='stylesheet' type='text/css'/>
    <link href='<?php echo base_url();?>swagger/css/reset.css' media='print' rel='stylesheet' type='text/css'/>
    <link href='<?php echo base_url();?>swagger/css/print.css' media='print' rel='stylesheet' type='text/css'/>
    <script src='<?php echo base_url();?>swagger/lib/jquery-1.8.0.min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/jquery.slideto.min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/jquery.wiggle.min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/jquery.ba-bbq.min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/handlebars-2.0.0.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/underscore-min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/backbone-min.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/swagger-ui.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/highlight.7.3.pack.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/marked.js' type='text/javascript'></script>
    <script src='<?php echo base_url();?>swagger/lib/swagger-oauth.js' type='text/javascript'></script>

    <script type="text/javascript">
        $(function () {
            var url = window.location.search.match(/url=([^&]+)/);
            if (url && url.length > 1) {
                url = decodeURIComponent(url[1]);
            } else {
                url = "<?php echo site_url('swagger/pet_api.json');?>";
            }
            window.swaggerUi = new SwaggerUi({
                url: url,
                dom_id: "swagger-ui-container",
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function(swaggerApi, swaggerUi){
                    if(typeof initOAuth == "function") {
                        initOAuth({
                            clientId: "your-client-id",
                            realm: "your-realms",
                            appName: "your-app-name"
                        });
                    }

                    $('pre code').each(function(i, e) {
                        hljs.highlightBlock(e)
                    });

                    addApiKeyAuthorization();
                },
                onFailure: function(data) {
                    log("Unable to Load SwaggerUI");
                },
                docExpansion: "none",
                apisSorter: "alpha",
                showRequestHeaders: false
            });

            function addApiKeyAuthorization(){
                var key = encodeURIComponent($('#input_apiKey')[0].value);
                if(key && key.trim() != "") {
                    var apiKeyAuth = new SwaggerClient.ApiKeyAuthorization("api_key", key, "query");
                    window.swaggerUi.api.clientAuthorizations.add("api_key", apiKeyAuth);
                    log("added key " + key);
                }
            }

            $('#input_apiKey').change(addApiKeyAuthorization);

            // if you have an apiKey you would like to pre-populate on the page for demonstration purposes...
            /*
             var apiKey = "myApiKeyXXXX123456789";
             $('#input_apiKey').val(apiKey);
             */

            window.swaggerUi.load();

            function log() {
                if ('console' in window) {
                    console.log.apply(console, arguments);
                }
            }
        });
    </script>
</head>

<body class="swagger-section">
<div id='header'>
    <div class="swagger-ui-wrap">
        <a id="logo" href="http://swagger.io">swagger</a>
        <form id='api_selector'>
            <div class='input'><input placeholder="http://example.com/api" id="input_baseUrl" name="baseUrl" type="text"/></div>
            <div class='input'><input placeholder="api_key" id="input_apiKey" name="apiKey" type="text"/></div>
            <div class='input'><a id="explore" href="#">Explore</a></div>
        </form>
    </div>
</div>

<div id="message-bar" class="swagger-ui-wrap">&nbsp;</div>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
<script type="text/javascript">
    /*function link_to_bookmark($this) {
     var tag = $($this).data('tag');
     var path = $($this).data('path');
     $('#endpointListTogger_'+tag).trigger('click');
     $('#'+tag+'_'+path+'_content').show();
     var endpoint = $('#'+tag+'_endpoint_list li.endpoint');

     return false;
     }*/
    var opening_tag = '';
    $(function () {
        $('#swagger-ui-container').on('click','a.bookmark_link',function(e){
            e.preventDefault();
            var tag = $(this).data('tag');
            var path = $(this).data('path');
            if(!$('#'+tag+'_endpoint_list').is(":visible")) {
                $('#endpointListTogger_'+tag).trigger('click');
            }

            //$('#endpointListTogger_'+tag).trigger('click');
            $('#'+tag+'_'+path+'_content').show();
            $('html, body').animate({
                scrollTop: $('#'+tag+'_'+path+'_content').offset().top
            }, 1000);
        });
    });
</script>

</body>
</html>
