<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_html_theme extends qa_html_theme_base {

		function doctype() {
			qa_html_theme_base::doctype();
		}
		
		function head()
		{
			$this->output(
				'<head>',
				'<meta http-equiv="content-type" content="'.$this->content['content_type'].'"/>'
			);
			
			$this->donut_default_meta();
			$this->head_title();
			$this->head_metas();
			$this->head_css();
			$this->donut_utility_for_old_ie();
			$this->head_links();
			$this->head_lines();
			$this->head_script();
			$this->head_custom();
			
			$this->output('</head>');
		}

		function head_script() // change style of WYSIWYG editor to match theme better
		{
			qa_html_theme_base::head_script();
			$js_paths = array(
				'bootstrap' => 'js/bootstrap.min.js',
				'donut'     => 'js/donut.js',
				);
			if ($this->template == 'admin') {
				$js_paths['admin'] = 'js/admin.js' ;
			}
			
			if (DONUT_ACTIVATE_PROD_MODE) {
				$cdn_js_paths = array(
					'bootstrap' => donut_opt::BS_JS_CDN ,
					);
				unset($js_paths['bootstrap']);
				$this->donut_resources($cdn_js_paths , 'js' , TRUE );
			}

			$this->donut_resources($js_paths , 'js');

		}

		function head_css()
		{
			qa_html_theme_base::head_css();
			$css_paths = array(
					'bootstrap' => 'css/bootstrap.min.css',
					'donut'     => 'css/donut.css',
					'fonts'     => 'css/font-awesome.min.css',
					);
			if ($this->template == 'admin') {
				$css_paths['admin'] = 'css/admin.css' ;
			}else {
				$css_paths['donut_responsive'] = 'css/donut-responsive.css' ;
			}

			if (DONUT_ACTIVATE_PROD_MODE) {
				$cdn_css_paths = array(
					'bootstrap' => donut_opt::BS_CSS_CDN ,
					// 'bootstrap_theme' => donut_opt::BS_THEME_CSS_CDN ,
					'fonts' => donut_opt::FA_CDN ,
					);
				unset($css_paths['bootstrap']);
				unset($css_paths['fonts']);
				$this->donut_resources($cdn_css_paths , 'css' , TRUE );
			}

			$this->donut_resources($css_paths , 'css');
		}

		function body_content()
		{
			$sub_navigation = @$this->content['navigation']['sub'];
			if ($this->template === 'admin') {
				unset($this->content['navigation']['sub']);
			}
			$navigation = &$this->content['navigation'];
			if (isset($navigation['cat'])) {
				donut_remove_brackets($navigation['cat']);
			}
			$this->body_prefix();
			$this->notices();
			
			if ($this->template !== 'question') {
				$this->output('<main class="donut-masthead">');
				$this->output('<div class="container">');
				$this->output('<div class="page-title">');
				$this->page_title_error();		
				$this->output('</div>');
				$this->output('</div>');
				$this->output('</main>');
			}

			$this->output('<div class="qa-body-wrapper">', '');

			$this->widgets('full', 'top');
			$this->header();
			$this->widgets('full', 'high');
			
			if (count($sub_navigation)) {
				// create the left side bar 
				$this->left_side_bar($sub_navigation);
			}

			$this->main();

			if ($this->template !== 'admin') {
				$this->sidepanel();
			}

			$this->widgets('full', 'low');
			$this->footer();
			$this->widgets('full', 'bottom');
			
			$this->output('</div> <!-- END body-wrapper -->');
			
			$this->body_suffix();
		}

		function main()
		{
			$content=$this->content;

			$this->output('<div class="qa-main'.(@$this->content['hidden'] ? ' qa-main-hidden' : '').'">');
			
			if (!empty($this->content['navigation']['sub']) || $this->template == 'admin') {
				$this->donut_sidebar_toggle_nav_btn();
			}

			$this->widgets('main', 'top');
			
			if($this->template == 'question') {
				$this->page_title_error();
			}	

			if (!empty($this->content['navigation']['sub']) || $this->template == 'admin') {

				$this->output('<div class="row hidden-xs subnav-row">');
				$this->nav_main_sub();
				$this->output('</div>');

			}

			$this->widgets('main', 'high');

			/*if (isset($content['main_form_tags']))
				$this->output('<form '.$content['main_form_tags'].'>');*/
				
			$this->main_parts($content);
		
			/*if (isset($content['main_form_tags']))
				$this->output('</form>');*/
				
			$this->widgets('main', 'low');

			$this->page_links();
			$this->suggest_next();
			
			$this->widgets('main', 'bottom');

			$this->output('</div> <!-- END qa-main -->', '');
		}

		function nav_user_search() // outputs login form if user not logged in
		{
			qa_html_theme_base::nav_user_search();
			
			if (!qa_is_logged_in()) {
				$login=@$this->content['navigation']['user']['login'];
				
				if (isset($login) && !QA_FINAL_EXTERNAL_USERS) {
					$this->output(
						'<!--[Begin: login form]-->',				
						'<form id="qa-loginform" action="'.$login['url'].'" method="post">',
							'<input type="text" id="qa-userid" name="emailhandle" placeholder="'.trim(qa_lang_html(qa_opt('allow_login_email_only') ? 'users/email_label' : 'users/email_handle_label'), ':').'" />',
							'<input type="password" id="qa-password" name="password" placeholder="'.trim(qa_lang_html('users/password_label'), ':').'" />',
							'<div id="qa-rememberbox"><input type="checkbox" name="remember" id="qa-rememberme" value="1"/>',
							'<label for="qa-rememberme" id="qa-remember">'.qa_lang_html('users/remember').'</label></div>',
							'<input type="hidden" name="code" value="'.qa_html(qa_get_form_security_code('login')).'"/>',
							'<input type="submit" value="'.$login['label'].'" id="qa-login" name="dologin" />',
						'</form>',				
						'<!--[End: login form]-->'
					);
					
					unset($this->content['navigation']['user']['login']); // removes regular navigation link to log in page
				}
			}
			
		}
		
		function logged_in() 
		{
			if (qa_is_logged_in()) // output user avatar to login bar
				$this->output(
					'<div class="qa-logged-in-avatar">',
					QA_FINAL_EXTERNAL_USERS
					? qa_get_external_avatar_html(qa_get_logged_in_userid(), 24, true)
					: qa_get_user_avatar_html(qa_get_logged_in_flags(), qa_get_logged_in_email(), qa_get_logged_in_handle(),
						qa_get_logged_in_user_field('avatarblobid'), qa_get_logged_in_user_field('avatarwidth'), qa_get_logged_in_user_field('avatarheight'),
						24, true),
            		'</div>'
            	);				
			
			qa_html_theme_base::logged_in();
			
			if (qa_is_logged_in()) { // adds points count after logged in username
				$userpoints=qa_get_logged_in_points();
				
				$pointshtml=($userpoints==1)
					? qa_lang_html_sub('main/1_point', '1', '1')
					: qa_lang_html_sub('main/x_points', qa_html(number_format($userpoints)));
						
				$this->output(
					'<span class="qa-logged-in-points">',
					'('.$pointshtml.')',
					'</span>'
				);
			}
		}
    
		function body_header() // adds login bar, user navigation and search at top of page in place of custom header content
		{
			if (!empty($this->content['navigation']['main'])) {
				$this->output($this->donut_nav_bar($this->content['navigation']));
				unset($this->content['navigation']['main']);
			}
			
			/*$this->output('<div id="qa-login-bar"><div id="qa-login-group">');
			$this->nav_user_search();
            $this->output('</div></div>');*/
        }
		
		function header_custom() // allows modification of custom element shown inside header after logo
		{
			if (isset($this->content['body_header'])) {
				$this->output('<div class="header-banner">');
				$this->output_raw($this->content['body_header']);
				$this->output('</div>');
			}
		}
		
		function header() // removes user navigation and search from header and replaces with custom header content. Also opens new <div>s
		{	
			$this->output('<div class="qa-header">');
			
			// $this->logo();						
			$this->header_clear();
			$this->header_custom();

			$this->output('</div> <!-- END qa-header -->', '');

			$this->output('<div class="qa-main-shadow">', '');
			$this->output('<div class="qa-main-wrapper">', '');
			// $this->nav_main_sub();
			// $this->page_title_error();		
		}

		function page_links_item($page_link)
		{
			$active_class   = (@$page_link['type'] === 'this') ? ' active' : '' ;
			$disabled_class = (@$page_link['type'] === 'ellipsis') ? ' disabled' : '' ;
			$this->output('<li class="qa-page-links-item'.$active_class.$disabled_class.'">');
			$this->page_link_content($page_link);
			$this->output('</li>');
		}

		/**
		 * removes sidebar for user profile pages
		 * @return null
		 */
		function sidepanel() 
		{
			if ($this->template!='user')
				qa_html_theme_base::sidepanel();
		}

		function left_side_bar($sub_navigation)
		{
			
			$this->output('<div class="qa-left-side-bar" id="sidebar" role="navigation">', '');
			if (count($sub_navigation)) {

				$this->output('<div class="list-group">', '');

				foreach ($sub_navigation as $key => $sub_navigation_item) {
					$this->donut_nav_side_bar_item($sub_navigation_item);
				}
				$this->output('</div>', '');
				if ($this->template === 'admin') {
					unset($this->content['navigation']['sub']);	
				}
			}
			$this->output('</div>', '<!-- END of left-side-bar -->');
		}

		function a_selection($post)
		{
			$this->output('<div class="qa-a-selection">');
			
			if (isset($post['select_tags']))
				$this->post_hover_button($post, 'select_tags', '', 'qa-a-select');
			elseif (isset($post['unselect_tags']))
				$this->post_hover_button($post, 'unselect_tags', '', 'qa-a-unselect');
			elseif ($post['selected'])
				$this->output('<div class="qa-a-selected"> <span class="fa fa-check"></span> </div>');
			
			if (isset($post['select_text']))
				$this->output('<div class="qa-a-selected-text">'.@$post['select_text'].'</div>');
			
			$this->output('</div>');
		}

		/**
		 * prevent display of regular footer content (see body_suffix()) and replace with closing new <div>s
		 * @return  null
		 */
		function footer() 
		{
			$this->output('</div> <!-- END main-wrapper -->');
			$this->output('</div> <!-- END main-shadow -->');		
		}		

		/**
		 * add RSS feed icon after the page title
		 * @return null 
		 */
		function feed_link()
		{
			$feed=@$this->content['feed'];
			
			if (!empty($feed))
				$this->output('<a href="'.$feed['url'].'" title="'.@$feed['label'].'" class="qa-rss-feed"><i class="fa fa-rss qa-rss-icon" ></i></a>');
		}

		function page_title_error()
		{
			$favorite=@$this->content['favorite'];
			
			if (isset($favorite))
				$this->output('<form '.$favorite['form_tags'].'>');
			
			$this->feed_link();
				
			$this->output('<h1>');
			$this->favorite();
			$this->title();
			$this->output('</h1>');

			if (isset($this->content['error']))
				$this->error(@$this->content['error']);

			if (isset($favorite)) {
				$this->form_hidden_elements(@$favorite['form_hidden']);
				$this->output('</form>');
			}
		}

		/**
		 * add view count to question list
		 * @param  array $q_item 
		 * @return null 
		 */
		function q_item_stats($q_item) 
		{
			$this->output('<div class="qa-q-item-stats">');
			
			$this->voting($q_item);
			$this->a_count($q_item);
			// qa_html_theme_base::view_count($q_item);

			$this->output('</div>');
		}

		function post_meta($post, $class, $prefix=null, $separator='<br/>')
		{
			$this->output('<span class="'.$class.'-meta">');
			
			if (isset($prefix))
				$this->output($prefix);
			
			$order=explode('^', @$post['meta_order']);
			
			foreach ($order as $element)
				switch ($element) {
					case 'what':
						$this->post_meta_what($post, $class);
						break;
						
					case 'when':
						$this->post_meta_when($post, $class);
						break;
						
					case 'where':
						$this->post_meta_where($post, $class);
						break;
						
					case 'who':
						$this->post_meta_who($post, $class);
						break;
				}
				
			$this->post_meta_flags($post, $class);
			
			if (!empty($post['what_2'])) {
				$this->output($separator);
				
				foreach ($order as $element)
					switch ($element) {
						case 'what':
							$this->output('<span class="'.$class.'-what">'.$post['what_2'].'</span>');
							break;
						
						case 'when':
							$this->output_split(@$post['when_2'], $class.'-when');
							break;
						
						case 'who':
							$this->output_split(@$post['who_2'], $class.'-who');
							break;
					}
			}
			$this->donut_view_count($post);
			$this->output('</span>');
		}

		function view_count($q_item) // prevent display of view count in the usual place
		{	
			if ($this->template=='question')
				qa_html_theme_base::view_count($q_item);
		}
		
		function body_suffix() // to replace standard Q2A footer
        {
			$this->output('<footer class="donut-footer">');
			$this->output('<div class="container">');

			qa_html_theme_base::footer();
			$this->output('</div>');
			$this->output('</footer> <!-- END footer -->', '');
        }

        function post_hover_button($post, $element, $value, $class)
        {
        	if (isset($post[$element])){
        		$icon = donut_get_voting_icon($element);
        		$this->output('<button '.$post[$element].' type="submit" value="'.$value.'" class="'.$class.'-button"/> '.$icon.'</button>');
        	}
        }
        
        function post_disabled_button($post, $element, $value, $class)
        {
        	if (isset($post[$element])){
        		$icon = donut_get_voting_icon($element);
        		$this->output('<button '.$post[$element].' type="submit" value="'.$value.'" class="'.$class.'-disabled" disabled="disabled"/> '.$icon.'</button>');
        	}
        }

		function form_button_data($button, $key, $style)
		{
			$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
			
			$this->output('<button'.rtrim(' '.@$button['tags']).' title="'.@$button['popup'].'" type="submit"'.
				(isset($style) ? (' class="'.$baseclass.'"') : '').'>'.@$button['label'].'</button>');
		}

		/**
		 * prints the favorite button
		 * @param  array $tags  parameters
		 * @param  [type] $class class
		 * @return null 
		 */
		function favorite_button($tags, $class)
		{
			if (isset($tags)){
				$icon = donut_get_fa_icon('heart');
				$this->output('<button '.$tags.' type="submit" value="" class="'.$class.'-button"/> '.$icon.'</button>');
			}
		}

		/**
		 * the feed icon with a link
		 * @return null
		 */
		function feed()
		{
			$feed=@$this->content['feed'];
			
			if (!empty($feed)) {
				$icon = donut_get_fa_icon('rss');
				$this->output('<div class="qa-feed">');
				/*$this->output('<span class="qa-feed-icon">');
				$this->output($icon);
				$this->output('</span>');*/
				$this->output('<a href="'.$feed['url'].'" class="qa-feed-link"> <span class="icon-wrapper"> <span class="qa-feed-icon">'.$icon.' </span></span>'.@$feed['label'].'</a>');
				$this->output('</div>');
			}
		}

		/**
		 * Attribution link for the theme which adds the authors name 
		 * @return [type] [description]
		 */
		function attribution()
		{
			/*Please do not remove this as you are using this for free . I will appriciate if you keep this on your site */
			$this->output(
				'<div class="qa-attribution">',
				'&nbsp;| Donut Theme by <a href="http://amiyasahu.com">Amiya Sahu</a>',
				'</div>'
			);

			qa_html_theme_base::attribution();
		}

		/**
		 * beautifies the default waiting template with a font aswome icon 
		 * @return null
		 */
		function waiting_template()
		{
			$this->output('<span id="qa-waiting-template" class="qa-waiting fa fa-spinner fa-spin"></span>');
		}

		/**
		 * beautifies the default notice 
		 * @param  array $notice notice parameters
		 * @return null
		 */
		function notice($notice)
		{
			$this->output('<div class="qa-notice alert alert-info text-center alert-dismissible" role="alert" id="'.$notice['id'].'">');
			
			if (isset($notice['form_tags']))
				$this->output('<form '.$notice['form_tags'].'>');
			
			$this->output('<button '.$notice['close_tags'].' type="submit" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
			
			$this->output_raw($notice['content']);
			
			
			if (isset($notice['form_tags'])) {
				$this->form_hidden_elements(@$notice['form_hidden']);
				$this->output('</form>');
			}
			
			$this->output('</div>');
		}
		
		/**
		 * prints the navbar search on the top 
		 * @return null
		 */
		function search()
		{
			$search=$this->content['search'];
			
			$this->output(
				'<form class="navbar-form pull-right" role="form" '.$search['form_tags'].'>',
				@$search['form_extra']
			);
			
			$this->search_field($search);
			// $this->search_button($search);
			
			$this->output(
				'</form>'
			);
		}

		/**
		 * prints the search field 
		 * @param  array $search 
		 * @return null
		 */
		function search_field($search)
		{
			$this->output(
				'<div class="input-group">',
					'<input type="text" '.$search['field_tags'].' value="'.@$search['value'].'" class="qa-search-field" placeholder="'.$search['button_label'].'"/>');
			$this->search_button($search);
			$this->output('</div>');
		}

		/**
		 * prints the aearch button
		 * @param  array $search 
		 * @return null 
		 */
		function search_button($search)
		{
			$this->output('<span class="input-group-btn">');
			$this->output('<button type="submit" value="" class="btn qa-search-button" ><span class="fa fa-search"></span></button>');
			$this->output('</span>');
		}

		/**
		 * prints the css path
		 * @param  string  $path     path of the css file
		 * @param  boolean $external weather it is relative to the theme or a external to the theme 
		 * @return null
		 */
		function donut_css($path , $external = false)
		{
			if ($external) {
				$full_path = $path ;
			}else {
				$full_path = $this->rooturl.$path ;
			}

			if (!empty($path)) {
				$this->output('<link rel="stylesheet" type="text/css" href="'.$full_path.'"/>' );
			}
		}

		/**
		 * prints the js path
		 * @param  string  $path     path of the js file
		 * @param  boolean $external weather it is relative to the theme or a external to the theme 
		 * @return null
		 */
		function donut_js($path , $external = false)
		{
			if ($external) {
				$full_path = $path ;
			}else {
				$full_path = $this->rooturl.$path ;
			}

			if (!empty($path)) {
				$this->output('<script src="'.$full_path.'" type="text/javascript"></script>' );
			}
		}

		/**
		 * prints the CSS and JS links 
		 * @param  array  $paths    list of the resources
		 * @param  string  $type     type of the resource css or js 
		 * @param  boolean $external weather it is relative to the theme or a external to the theme 
		 * @return null
		 */
		function donut_resources($paths , $type = 'css' , $external = false )
		{
			if (count($paths)) {
				foreach ($paths as $key => $path) {
					if ($type ==='js') {
						$this->donut_js($path , $external) ;
					}else if ($type === 'css'){
						$this->donut_css($path , $external) ;
					}
				}
			}
		}

		/**
		 * prints the complete navbar
		 * @param  $navigation
		 * @return text
		 */
		function donut_nav_bar($navigation)
		{
			$title = qa_opt('site_title') ;
			$home_url = qa_opt('site_url') ;
			ob_start();
			?>
			<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			      <div class="container">
			        <div class="navbar-header">
			          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
			            <span class="sr-only">Toggle navigation</span>
			            <span class="icon-bar"></span>
			            <span class="icon-bar"></span>
			            <span class="icon-bar"></span>
			          </button>
			           <?php $this->logo(); ?>
			          <!-- <a class="navbar-brand first-letter-logo" href="<?php echo $home_url ;?>">D</a> -->
			        </div>
			        <div class="donut-navigation">
				        <ul class="nav navbar-nav navbar-right user-nav">
				        	<?php $this->donut_user_drop_down(); ?>
				        </ul>
				        <div class="navbar-collapse collapse main-nav">
				        	<?php $this->search(); ?>	
				        	<ul class="nav navbar-nav inner-drop-nav">
				        	    <?php $this->donut_nav_bar_main_links($navigation['main']); ?>
				        	</ul>
				        </div>
				        		        		
			        </div>

			      </div>
			</nav>
			<?php
			return ob_get_clean();
		}

		/**
		 * grabs the sub-nav links for the navigation items 
		 * @param  array $navigation navigation links
		 * @return null
		 */
		function donut_nav_bar_main_links($navigation)
		{
			if (count($navigation)) {
				foreach ($navigation as $key => $nav_item) {
					if ($key=='questions') {
						$sub_nav = donut_get_sub_navigation('questions' , $this->template);
						if (count($sub_nav)) {
							$this->donut_nav_bar_drop_down($nav_item, $sub_nav );
						}else {
							$this->donut_nav_bar_item($nav_item);
						}
					}else if ($key=='unanswered') {
						$sub_nav = donut_get_sub_navigation('unanswered');
						if (count($sub_nav)) {
							$this->donut_nav_bar_drop_down($nav_item, $sub_nav );
						}else {
							$this->donut_nav_bar_item($nav_item);
						}
					}else if ($key=='user') {
						$sub_nav = donut_get_sub_navigation('users');
						if (count($sub_nav)) {
							$this->donut_nav_bar_drop_down($nav_item, $sub_nav );
						}else {
							$this->donut_nav_bar_item($nav_item);
						}
					}else if ($key=='admin') {
						$sub_nav = donut_get_sub_navigation('admin');
						if (count($sub_nav)) {
							foreach ($sub_nav as $key => &$sub_nav_item) {
								$sub_nav_item['icon']='cog';
							}
							$this->donut_nav_bar_drop_down($nav_item, $sub_nav );
						}else {
							$this->donut_nav_bar_item($nav_item);
						}
					} else {
						$this->donut_nav_bar_item($nav_item);
					}
				}
			}
		}

		/**
		 * nav item for the sidebar 
		 * @param  array $nav_item navigation item
		 * @return null
		 */
		function donut_nav_side_bar_item($nav_item)
		{
			$class = (!!@$nav_item['selected']) ? ' active' : '' ;
			$icon = (!!@$nav_item['icon']) ? donut_get_fa_icon(@$nav_item['icon']) : '' ;
			$this->output('<a href="'.$nav_item['url'].'" class="list-group-item '.$class.'">'.$icon . $nav_item['label'].'</a>');
		}

		/**
		 * prints a single nav-bar item 
		 * @param  array $nav_item navigation item 
		 * @return null
		 */
		function donut_nav_bar_item($nav_item)
		{
			$class  = (!!@$nav_item['class']) ? $nav_item['class'] .' ' : '' ;
			$class .= (!!@$nav_item['selected']) ? 'active' : '' ;
			
			if (!empty($class)) {
				$class = 'class="'.$class.'"' ;
			}

			$icon   = (!!@$nav_item['icon']) ? donut_get_fa_icon(@$nav_item['icon']) : '' ;

			$this->output('<li '.$class.'><a href="'.$nav_item['url'].'">'.$icon . $nav_item['label'].'</a></li>');
		}

		/**
		 * Prints the drop down menu 
		 * @param  array $nav_item      the navigation item 
		 * @param  attay $sub_nav_items sub-nav items to be displayed 
		 * @return null
		 */
		function donut_nav_bar_drop_down($nav_item , $sub_nav_items)
		{
			$class = (!!@$nav_item['selected']) ? 'active' : '' ;
			
			if (!empty($sub_nav_items) && count($sub_nav_items)) {
				$nav_item['class'] = "dropdown-split-left" ;
				$this->donut_nav_bar_item($nav_item);
				$this->output('<li class="dropdown dropdown-split-right hidden-xs '.$class.'">');
				$this->output('<a href="#" class="dropdown-toggle transparent" data-toggle="dropdown"><i class="fa fa-caret-down"></i></a>');
				$this->output('<ul class="dropdown-menu" role="menu">');
				foreach ($sub_nav_items as $key => $sub_nav_item) {
					$this->donut_nav_bar_item($sub_nav_item);
				}
				$this->output('</ul>');
				$this->output('</li>');
			}else {
				$this->donut_nav_bar_item($nav_item);
			}
		}

		/**
		 * prints sidebar navigation 
		 * @return  null
		 */
		function donut_sidebar_toggle_nav_btn()
		{
			$this->output('<div class="row">');
				$this->output('<div class="pull-left col-xs-12 visible-xs side-toggle-button">');
					$this->output('<button type="button" class="btn btn-primary btn-xs" data-toggle="offcanvas">');
						$this->output('<i class="fa fa-chevron-right toggle-icon"></i>');
					$this->output('</button>');
				$this->output('</div>');
			$this->output('</div>');
		}

		/**
		 * prints the defult meta and view ports 
		 * @return  null
		 */
		function donut_default_meta()
		{
			$this->output_raw('<meta charset="utf-8">');
			$this->output_raw('<meta name="viewport" content="width=device-width, initial-scale=1">');
			$this->output_raw('<meta name="description" content="">');
			$this->output_raw('<meta name="author" content="">');
		}

		/**
		 * prints the favicon icon
		 * @return  null
		 */
		function donut_favicon()
		{
			$this->output_raw('<link rel="shortcut icon" href="favicon.ico">');
		}

		/**
		 * prints the view count 
		 * @param  array 
		 * @return null
		 */
		function donut_view_count($post)
		{
			if (!empty($post['views']) && $this->template !== 'question') {
				$this->output('<span class="qa-q-item-view-count">');
				$this->output(' | <i class="fa fa-eye"></i>');
				$this->output_split(@$post['views'], 'q-item-view');
				$this->output('</span>');
			}
		}

		/**
		 * adds support for old IE browsers 
		 * 
		 */
		function donut_utility_for_old_ie()
		{
			$this->output('
					<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
					   <!--[if lt IE 9]>
					     <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
					     <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
					<![endif]-->
				');
		}

		/**
		 * prints the drop down for the user 
		 * 
		 */
		function donut_user_drop_down(){
			if (qa_is_logged_in()) {
				require_once DONUT_THEME_BASE_DIR . '/templates/user-loggedin-drop-down.php' ;
			}else {
				require_once DONUT_THEME_BASE_DIR . '/templates/user-login-drop-down.php' ;
			}
		}
	}
/*
	Omit PHP closing tag to help avoid accidental output
*/