<?php
/**
 * Plugin Name: Modify WP Job Manager fields adding salary
 * Plugin URI:  https://github.com/WPsites/wp-job-manager-fields
 * Description: An example plugin for adding a salary field to the WP Job Manager submission and search form.
 * Author:      Simon @ WPsites - forked from: https://github.com/Astoundify/wp-job-manager-fields
 * Version:     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Modify_Job_Manager_Fields {

    /**
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Make sure only one instance is only running.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param void
	 * @return object $instance The one true class instance.
	 */
	public static function instance() {
		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param void
	 * @return void
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set some smart defaults to class variables.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param void
	 * @return void
	 */
	private function setup_globals() {
		$this->file         = __FILE__;

		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file ); 
	}

	/**
	 * Hooks and filters.
	 *
	 * We need to hook into a couple of things:
	 * 1. Output fields on frontend, and save.
	 * 2. Output fields on backend, and save (done automatically).
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param void
	 * @return void
	 */
	private function setup_actions() {
		/**
		 * Filter the default fields that ship with WP Job Manager.
		 * The `form_fields` method is what we use to add our own custom fields.
		 */
		add_filter( 'submit_job_form_fields', array( $this, 'form_fields' ) );

		/**
		 * When WP Job Manager is saving all of the default field data, we need to also
		 * save our custom fields. The `update_job_data` callback is what does this.
		 */
		add_action( 'job_manager_update_job_data', array( $this, 'update_job_data' ), 10, 2 );

		/**
		 * Filter the default fields that are output in the WP admin when viewing a job listing.
		 * The `job_listing_data_fields` adds the same fields to the backend that we added to the front.
		 *
		 * We do not need to add an additional callback for saving the data, as this is done automatically.
		 */
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'job_listing_data_fields' ) );
        
        	/**
		 * Add some new fields into the search/filter form
		 */
        	add_action('job_manager_job_filters_search_jobs_end', array( $this, 'add_search_fields' ), 10);
        
		/**
    	 	* Add some new fields into the search/filter form
		*/
        	add_filter( 'job_manager_get_listings', array( $this, 'query_our_search_fields' ), 10);
        
	}
    
    
	/**
	 * Hook our new search fields into the get_job_listings() query of wp job manager
	 *
	 */
	function query_our_search_fields( $query_args ) {
        
	        //just return if form data not posted
	        if ( !isset($_POST['form_data']) )
	            return $query_args;
	            
	        $form_data = wp_parse_args($_POST['form_data'], array('salary_from' => '', 'salary_to' => ''));
	        
	        //salary from
	        if ( preg_match("/[0-9]+/", $form_data['salary_from']) ){
	            
	        	if ($form_data['salary_what'] === 'Per Month')
	                	$form_data['salary_from'] = $form_data['salary_from'] *12;
	            
	        	$query_args['meta_query'][] = array(
	    			'key'     => '_salary',
	    			'value'   => sanitize_text_field($form_data['salary_from']) ,
	                	'type'    => 'NUMERIC',
	    			'compare' => '>='
	    		);
	        
	        }
	
	        //salary to
	        if ( preg_match("/[0-9]+/", $form_data['salary_to']) ){
	            
	        	if ($form_data['salary_what'] === 'Per Month')
	        		$form_data['salary_to'] = $form_data['salary_to'] *12;
	                
	        	$query_args['meta_query'][] = array(
	        		'key'     => '_salary',
	    			'value'   => sanitize_text_field($form_data['salary_to']) ,
	                	'type'    => 'NUMERIC',
	    			'compare' => '<='
	    		);
	        
	        }
	
	        return $query_args;
	}
    
	/**
	 * Add search fields to filter form.
	 *
	 */
	function add_search_fields( $atts ) {
	        ?>
	        	<ul class="salary">
			<li>
	                    <input type="text" name="salary_from" name="salary_from" id="salary_from" value="" placeholder="Salary from £" /> <input type="text" class="salary_to" name="salary_to" name="salary_to" placeholder="Salary to £" value="" />
	                    <select name="salary_what" class="salary_what"><option>Per Annum</option><option>Per Month</option></select>
	                </li>
			</ul>
	        <?php
	}

	/**
	 * Add fields to the submission form.
	 *
	 * Currently the fields must fall between two sections: "job" or "company". Until
	 * WP Job Manager filters the data that passes to the registration template, these are the
	 * only two sections we can manipulate.
	 *
	 * You may use a custom field type, but you will then need to filter the `job_manager_locate_template`
	 * to search in `/templates/form-fields/$type-field.php` in your theme or plugin.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param array $fields The existing fields
	 * @return array $fields The modified fields
	 */
	function form_fields( $fields ) {
		$fields[ 'job' ][ 'salary' ] = array(
			'label'       => 'Salary',  // The label for the field
			'type'        => 'text',           // file, job-description (tinymce), select, text
			'placeholder' => 'Annual Salary',     // Placeholder value
			'required'    => false,             // If the field is required to submit the form
			'priority'    => 3                 // Where should the field appear based on the others
		);

		/**
		 * Repeat this for any additional fields.
		 */

		return $fields;
	}

	/**
	 * When the form is submitted, update the data.
	 *
	 * All data is stored in the $values variable that is in the same
	 * format as the fields array.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param int $job_id The ID of the job being submitted.
	 * @param array $values The values of each field.
	 * @return void
	 */
	function update_job_data( $job_id, $values ) {
		/** Get the value of our "salary" field. */
		$salary = isset ( $values[ 'job' ][ 'salary' ] ) ? sanitize_text_field( $values[ 'job' ][ 'salary' ] ) : null;

		if ( $salary ){
    	
            		//replace comma in number to keep things simple
            		$salary = str_replace(',', "", $salary);
            
            		/** By using an underscore in the meta key name, we can prevent this from being shown in the Custom Fields metabox. */
			update_post_meta( $job_id, '_salary', $salary );
            
		}

		/**
		 * Repeat this process for any additional fields. Always escape your data.
		 */
	}

	/**
	 * Add fields to the admin write panel.
	 *
	 * There is a slight disconnect between the frontend and backend at the moment.
	 * The frontend allows for select boxes, but there is no way to output those in
	 * the admin panel at the moment.
	 *
	 * @since Custom fields for WP Job Manager 1.0
	 *
	 * @param array $fields The existing fields
	 * @return array $fields The modified fields
	 */
	function job_listing_data_fields( $fields ) {
		/**
		 * Add the field we added to the frontend, using the meta key as the name of the
		 * field. We do not need to separate these fields into "job" or "company" as they
		 * are all output in the same spot.
		 */
		$fields[ '_salary' ] = array(
			'label'       => 'Salary', // The field label
			'placeholder' => 'Annual salary',     // The default value when adding via backend.
			'type'        => 'text'            // text, textarea, checkbox, file
		);

		/**
		 * Repeat this for any additional fields.
		 */

		return $fields;
	}
}
add_action( 'init', array( 'Modify_Job_Manager_Fields', 'instance' ) );
