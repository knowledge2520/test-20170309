<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Front Controller
 */
class Pet_Controller extends Base_Controller
{
    protected $pager;
    protected $limit;

    /**
     * Site Title
     *
     * @var string
     */
    public $site_title = '';

    /**
     * Page Title
     *
     * @var string
     */
    public $page_title = '';

    /**
     * Page Meta Keywords
     *
     * @var string
     */
    public $page_meta_keywords = '';

    /**
     * Page Meta Description
     *
     * @var string
     */
    public $page_meta_description = '';

    /**
     * JS Calls on DOM Ready
     *
     * @var array
     */
    public $js_domready = array();

    /**
     * JS Calls on window load
     *
     * @var array
     */
    public $js_windowload = array();

    /**
     * Body classes
     *
     * @var array
     */
    public $body_class = array();
    /**
     * Current section
     *
     * @var string
     */
    public $current_section = '';

    public $theme_path = '../../themes/pet/';
    
    public $theme_layout = 'pet/index';

    public $page_css = '';

    //--------------------------------------------------------------------

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        
        // Pagination config
        $this->pager = array(
        		'full_tag_open'     => '<div class="navLinks">',
        		'full_tag_close'    => '</div>',
        		'first_link'         => '<img border="0" alt="" src="" class="viewnavicon">',
        		'next_link'         => '<img border="0" alt="" src="" class="viewnavicon">',
        		'prev_link'         => '<img border="0" alt="" src="" class="viewnavicon">',
        		'last_link'			=> '<img border="0" alt="" src="" class="viewnavicon">',
        		'next_tag_open'     => '<img border="0" alt="" src="" class="viewnavdelimiter"><a>',
        		'next_tag_close'    => '</a>',
        		'prev_tag_open'     => '<img border="0" alt="" src="" class="viewnavdelimiter"><a>',
        		'prev_tag_close'    => '</a>',
        		'first_tag_open'    => '<img border="0" alt="" src="" class="viewnavdelimiter"><a>',
        		'first_tag_close'   => '</a>',
        		'last_tag_open'     => '<img border="0" alt="" src="" class="viewnavdelimiter"><a>',
        		'last_tag_close'    => '</a>',
        		'cur_tag_open'      => '<img border="0" alt="" src="" class="viewnavdelimiter"><a class="current"><b>',
        		'cur_tag_close'     => '</b></a>',
        		'num_tag_open'      => '<img border="0" alt="" src="" class="viewnavdelimiter"><a>',
        		'num_tag_close'     => '</a>',
        );
//         $this->limit = $this->settings_lib->item('site.list_limit');
        $this->limit = 5;

        // Initialize array with assets we use site wide
        //css
        $this->assets_css = array();
        $this->assets_css['global'] = array(
            //<!-- BEGIN Bootstrap core CSS -->
            'bootstrap.min.css',
            //<!-- END Bootstrap core CSS -->
        );
        $this->assets_css['page_style'] = array(
        	//<!-- BEGIN Pet Widget Template CSS -->
        	'main.css',
        	'fancybox.css',
        	'flexslider.css',
        	//<!-- END Pet Widget Template CSS -->
        );
        
        //js
        $this->assets_js = array();
        $this->assets_js['core_plugin'] = array(
            //<!-- BEGIN CORE PLUGINS -->
            'jquery.js',
            'bootstrap.min.js',
        	'owl.carousel.min.js',
        	'ketchup.all.js',
        	'fancybox.js',
        	'flexslider.js',
        	'script.js',
        		
            //<!-- END CORE PLUGINS -->
        );
        $this->assets_js['page_script'] = array(
        );

        //load site helper
        $this->load->helper('site');
        //load app config
        $this->load->config('app');

        $this->site_title = $this->config->item('front_app_title');
    }//end __construct()

    //--------------------------------------------------------------------

    /**
     * Set CSS Meta
     */
    private function set_styles()
    {

        if (count($this->assets_css['global']) > 0)
        {
            $this->template->append_metadata('<!-- Bootstrap core CSS -->');
            foreach($this->assets_css['global'] as $asset)
            {
                $this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'themes/pet/css/' . $asset . '" media="screen" />');
            }
        }
        if (isset($this->assets_css['page_style']) && count($this->assets_css['page_style']) > 0)
        {
            $this->template->append_metadata('<!-- Pet Widget Template CSS -->');
            foreach($this->assets_css['page_style'] as $asset)
            {
                $this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'themes/pet/css/' . $asset . '" media="screen" />');
            }
        }
        if (isset($this->assets_css['custom_style']) && count($this->assets_css['custom_style']) > 0)
        {
        	foreach($this->assets_css['custom_style'] as $asset)
        	{
        		$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'themes/pet/css/' . $asset . '" media="screen" />');
        	}
        }
        // Webkit based browsers
        //$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/webkit.css" media="screen" />');

        // Internet Explorer styles
        $this->template->append_metadata('<link href="http://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">');
        $this->template->append_metadata('<link href="http://fonts.googleapis.com/css?family=Droid+Serif:400,700" rel="stylesheet" type="text/css">');
        $this->template->append_metadata('<link rel="stylesheet" id="googlefonts-css" href="//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,700,600,300" type="text/css" media="all">');
        
   	}

    /**
     * Set Javascript Meta
     */
    private function set_javascript()
    {
        if (count($this->assets_js['core_plugin']) > 0)
        {
            $this->template->append_scriptdata('<!-- BEGIN CORE PLUGINS -->');
            foreach($this->assets_js['core_plugin'] as $asset)
                if (stristr($asset, 'http') === FALSE)
                    $this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . 'themes/pet/js/' . $asset . '"></script>');
                else
                    $this->template->append_scriptdata('<script type="text/javascript" src="' . $asset . '"></script>');
        }
        if (isset($this->assets_js['page_plugin']) && count($this->assets_js['page_plugin']) > 0)
        {
            $this->template->append_scriptdata('<!--BEGIN PAGE LEVEL PLUGINS -->');
            foreach($this->assets_js['page_plugin'] as $asset)
                if (stristr($asset, 'http') === FALSE)
                    $this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . 'themes/pet/js/' . $asset . '"></script>');
                else
                    $this->template->append_scriptdata('<script type="text/javascript" src="' . $asset . '"></script>');
        }
        if (count($this->assets_js['page_script']) > 0)
        {
            $this->template->append_scriptdata('<!-- BEGIN PAGE LEVEL SCRIPTS -->');
            foreach($this->assets_js['page_script'] as $asset)
                if (stristr($asset, 'http') === FALSE)
                    $this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . 'themes/pet/js/' . $asset . '"></script>');
                else
                    $this->template->append_scriptdata($asset);
        }
        $this->template->append_scriptdata('<!--[if lt IE 9]><script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->');
       
    }
    /**
     * Prepare BASE Javascript
     */
    private function prepare_base_javascript()
    {
        $str = "<script type=\"text/javascript\">\n";

        if (count($this->js_domready) > 0)
        {
            $str.= "$(document).ready(function() {\n";
            $str.= implode("\n", $this->js_domready) . "\n";
            $str.= "});\n";
        }

        if (count($this->js_windowload) > 0)
        {
            $str.= "$(window).load(function() {\n";
            $str.= implode("\n", $this->js_windowload) . "\n";
            $str.= "});\n";
        }

        $str.= "</script>\n";
        $this->template->append_scriptdata($str);
    }
    /**
     * Renders page
     */
    public function render_page($page, $data = array())
    {
        // Renders the whole page
        $this->template
            ->set_metadata('keywords', $this->page_meta_keywords)
            ->set_metadata('description', $this->page_meta_description)
            ->set_metadata('author', $this->page_meta_author)
            ->set_metadata('canonical', site_url($this->uri->uri_string()), 'link');
            //->title($this->site_title,$this->page_title);

        $this->set_styles();


        // Set global template vars
        $this->template
            ->set('current_section', $this->current_section)
            //->set('user_logged_in', $this->ion_auth->logged_in())
            ->set('body_class', implode(' ', $this->body_class))
            ->set('theme_path', $this->theme_path);

        $this->template
            ->set_partial('header', 'partials/pet/header')
            ->set_partial('footer', 'partials/pet/footer')
            ->set_layout($this->theme_layout);

        $this->set_javascript();
        $this->prepare_base_javascript();

        // Renders the main layout
        $this->template->build($page, $data);
    }

}

/* End of file Front_Controller.php */
/* Location: ./application/core/Pet_Controller.php */