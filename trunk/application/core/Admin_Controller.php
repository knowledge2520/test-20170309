<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
error_reporting(E_ALL ^ E_STRICT);
/**
 * Admin Controller
 *
 */
date_default_timezone_set('Asia/Bangkok');
class Admin_Controller extends Authenticated_Controller
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
    
    public $theme_path = '';
    
    public $page_css = '';
    /**
     * Current section
     *
     * @var array
     */
    public $assets_js = array();
    /**
     * Current section
     *
     * @var array
     */
    public $assets_css = array();
    
    
    //--------------------------------------------------------------------

    /**
     * Class constructor - setup paging and keyboard shortcuts as well as
     * load various libraries
     *
     */
    public function __construct()
    {
        parent::__construct();

        //check file upload
        $this->load->helper(array('upload', 'site', 'listing'));
        $this->load->library('messages');
        $checked = check_file_upload();
        if($checked){
            $this->messages->add($checked, "error");
        }

        // Pagination config
        $this->pager = array(
            'full_tag_open'     => '<div class="pagination-right"><ul class="pagination">',
            'full_tag_close'    => '</ul></div>',
            'next_link'         => '&rarr;',
            'prev_link'         => '&larr;',
            'next_tag_open'     => '<li>',
            'next_tag_close'    => '</li>',
            'prev_tag_open'     => '<li>',
            'prev_tag_close'    => '</li>',
            'first_tag_open'    => '<li>',
            'first_tag_close'   => '</li>',
            'last_tag_open'     => '<li>',
            'last_tag_close'    => '</li>',
            'cur_tag_open'      => '<li class="active"><a href="#">',
            'cur_tag_close'     => '</a></li>',
            'num_tag_open'      => '<li>',
            'num_tag_close'     => '</li>',
        );
        $this->limit = ADMIN_ITEMS_PERPAGE;

        // Initialize array with assets we use site wide
        //css
        $this->assets_css['global'] = array(
        		//<!-- BEGIN GLOBAL MANDATORY STYLES -->
        		'fonts.gstatic.css',
        		'../global/plugins/font-awesome/css/font-awesome.min.css',
        		'../global/plugins/simple-line-icons/simple-line-icons.min.css',
        		'../global/plugins/bootstrap/css/bootstrap.min.css',
        		'../global/plugins/uniform/css/uniform.default.css',
        		'../global/plugins/bootstrap-switch/css/bootstrap-switch.min.css',
        		//<!-- END GLOBAL MANDATORY STYLES -->
        );
        $this->assets_css['theme_style'] = array(
        		//<!-- BEGIN THEME STYLES -->
        		'../global/css/components.css',
        		'../global/css/plugins.css',
        		'../layouts/layout/css/layout.css',
        		'../layouts/layout/css/themes/darkblue.css',
        		'../layouts/layout/css/custom.css',
        );
        
        //js
        $this->assets_js['core_plugin'] = array(
        		//<!-- BEGIN CORE PLUGINS -->
        		'../global/plugins/jquery.min.js',
        		'../global/plugins/jquery-migrate.min.js',
        		//<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
        		'../global/plugins/jquery-ui/jquery-ui.min.js',
        		'../global/plugins/bootstrap/js/bootstrap.min.js',
        		'../global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js',
        		'../global/plugins/jquery-slimscroll/jquery.slimscroll.min.js',
        		'../global/plugins/jquery.blockui.min.js',
        		'../global/plugins/js.cookie.min.js',
        		'../global/plugins/uniform/jquery.uniform.min.js',
        		'../global/plugins/bootstrap-switch/js/bootstrap-switch.min.js',
        		//<!-- END CORE PLUGINS -->
        );
        $this->assets_js['page_script'] = array(
        		'../global/scripts/app.js',
        		'../layouts/layout/scripts/layout.js',
        		'../layouts/global/scripts/quick-sidebar.js',
        		// '../layouts/layout/scripts/demo.js',     
        		// '../layouts/pages/scripts/table-advanced.js',
        );
        
        //load site helper
        $this->load->helper('site');
        //load app config
        $this->load->config('app');
        
        $this->theme_path = site_url() . '../themes/'.$this->config->item('layout_path');
        $this->site_title = $this->config->item('admin_app_title');
        
    }//end construct()

    //--------------------------------------------------------------------
    
    /**
     * Set CSS Meta
     */
    private function set_styles()
    {

    	if (count($this->assets_css['global']) > 0)
    	{
    		$this->template->append_metadata('<!-- BEGIN GLOBAL MANDATORY STYLES -->');
    		foreach($this->assets_css['global'] as $asset)
    		{
    			$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . '../themes/admin/css/' . $asset . '" media="screen" />');
    		}
    	}    	
    	if (isset($this->assets_css['page_style']) && count($this->assets_css['page_style']) > 0)
    	{
    		$this->template->append_metadata('<!-- BEGIN PAGE STYLES -->');
    		foreach($this->assets_css['page_style'] as $asset)
    		{
    			$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . '../themes/admin/css/' . $asset . '" media="screen" />');
    		}
    	}
    	if (count($this->assets_css['theme_style']) > 0)
    	{
    		$this->template->append_metadata('<!-- BEGIN THEME STYLES -->');
    		foreach($this->assets_css['theme_style'] as $asset)
    		{
    			$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . '../themes/admin/css/' . $asset . '" media="screen" />');
    		}
    	}
    
    	// Webkit based browsers
    	//$this->template->append_metadata('<link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/webkit.css" media="screen" />');
    
    	// Internet Explorer styles
    	$this->template->append_metadata('<!--[if IE 6]><link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/ie6.css" media="screen" /><![endif]-->');
    	$this->template->append_metadata('<!--[if IE 7]><link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/ie7.css" media="screen" /><![endif]-->');
    	$this->template->append_metadata('<!--[if IE 8]><link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/ie8.css" media="screen" /><![endif]-->');
    	$this->template->append_metadata('<!--[if IE 9]><link rel="stylesheet" type="text/css" href="' . $this->config->item('base_url') . 'assets/css/cross_browser/ie9.css" media="screen" /><![endif]-->');
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
    			$this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . '../themes/admin/js/' . $asset . '"></script>');
    		else
    			$this->template->append_scriptdata('<script type="text/javascript" src="' . $asset . '"></script>');
    	}
    	if (isset($this->assets_js['page_plugin']) && count($this->assets_js['page_plugin']) > 0)
    	{
    		$this->template->append_scriptdata('<!--BEGIN PAGE LEVEL PLUGINS -->');
    		foreach($this->assets_js['page_plugin'] as $asset)
    		if (stristr($asset, 'http') === FALSE)
    			$this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . '../themes/admin/js/' . $asset . '"></script>');
    		else
    			$this->template->append_scriptdata('<script type="text/javascript" ' . $asset . '></script>');
    	}
    	if (count($this->assets_js['page_script']) > 0)
    	{
    		$this->template->append_scriptdata('<!-- BEGIN PAGE LEVEL SCRIPTS -->');
    		foreach($this->assets_js['page_script'] as $asset)
    		if (stristr($asset, 'http') === FALSE)
    			$this->template->append_scriptdata('<script type="text/javascript" src="' . $this->config->item('base_url') . '../themes/admin/js/' . $asset . '"></script>');
    		else
    			$this->template->append_scriptdata('<script type="text/javascript" src="' . $asset . '"></script>');
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
    	$aside_data = SiteHelper::getAsideData();
    	// Renders the whole page
    	$this->template
    	->set_metadata('keywords', $this->page_meta_keywords)
    	->set_metadata('description', $this->page_meta_description)
    	->set_metadata('canonical', site_url($this->uri->uri_string()), 'link')
    	->title($this->site_title,$this->page_title);

    	$this->set_styles();
    
    
    	// Set global template vars
    	$this->template
    	->set('current_section', $this->current_section)
    	->set('user_logged_in', $this->ion_auth->logged_in())
    	->set('body_class', implode(' ', $this->body_class))
    	->set('aside_data',$aside_data)
    	->set('theme_path', $this->theme_path);
    
    	$this->template
    	->set_partial('flash_messages', 'partials/admin/flash_messages')
    	->set_partial('header', 'partials/admin/header')
    	->set_partial('aside', 'partials/admin/aside')
    	->set_partial('footer', 'partials/admin/footer');
    
    	$this->set_javascript();
    	$this->prepare_base_javascript();
    
    	// Renders the main layout
    	$this->template->build($page, $data);
    }
}

/* End of file Admin_Controller.php */
/* Location: ./application/core/Admin_Controller.php */