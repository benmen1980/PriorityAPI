<?php 
/**
 * @package NexVis\WordPress
 */
declare( strict_types = 1 );
namespace NexVis\WordPress;

abstract class WP_NVT_Plugin_Updater {

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	private $current_version;
	private $latest_version;
	/**
	 * The plugin directory.
	 *
	 * @var string
	 */
	private $directory;
	
	/**
	 * The plugin slug.
	 *
	 * @var string
	 */
	private $slug;
	
	private $gitusername;
	private $gitrepo;
	private $gitbranch;

	/**
	 * The constructor.
	 *
	 * @param string $current_version The current plugin version.
	 * @param string $slug            The slug of the plugin.
	 */
	public function __construct( string $current_version, string $directory, string $slug, string $gitusername, string $gitrepo, string $gitbranch = 'main' )
	{
		$this->current_version = $current_version;
		$this->directory = $directory;
		$this->slug = $slug;
		$this->gitusername = $gitusername;
		$this->gitrepo = $gitrepo;
		$this->gitbranch = $gitbranch;
	}

	/**
	 * Returns the latest version of the plugin.
	 *
	 * @return string|WP_Error The version number or instance of WP_Error.
	 */
	abstract protected function get_latest_version();
	
	/**
	 * Returns the latest version of the plugin.
	 *
	 * @return string|WP_Error The version number or instance of WP_Error.
	 */
	abstract protected function is_latest_version_available();

	/**
	 * Returns the url for the plugin.
	 *
	 * @return string The url.
	 */
	abstract protected function get_url();

	/**
	 * Returns the package url for the plugin.
	 *
	 * @return string The package url.
	 */
	abstract protected function get_package_url();
	
	/**
	 * Returns the commits URL of github repository.
	 *
	 * @return string The package url.
	 */
	abstract protected function get_commits_url();
	
	/**
	 * Returns the package url for the private repository.
	 *
	 * @return string The package url.
	 */
	abstract protected function get_private_package();

	/**
	 * Hook into the 'update_plugins' transients.
	 */
	public function init()
	{
		add_filter( 'transient_update_plugins', array( $this,  'filter_plugin_update_data' ) );
		add_filter( 'site_transient_update_plugins', array( $this,  'filter_plugin_update_data' ) );
		register_activation_hook( $this->directory . '/' . $this->slug . '.php', array( $this, 'filter_upgrader_post_install') );
		add_filter( 'upgrader_post_install', array( $this, 'filter_upgrader_post_install'), 10, 2 );
		//To avoid displaying new version number.
		add_action( $this->directory . '/' . $this->slug . '.php', 'change_plugin_update_message', 10, 2 );
		
		//Option to keep track of last update check (To avoid too many update check requests)
		$last_update_check = get_option( $this->slug."_last_update_check" );
		if(!$last_update_check)
			add_option( $this->slug."_last_update_check", time());
		
		$update_available = get_option( $this->slug."_update_available" );
		if(!$update_available)
			add_option( $this->slug."_update_available", 'no');

		
	}

	/**
	 * Filters the 'update_plugins' transients so that the plugin can be updated without using the WordPress.org repository.
	 *
	 * @param object $update_plugins The object detailing which plugins have updates available.
	 */
	public function filter_plugin_update_data( $update_plugins )
	{
		if ( ! is_object( $update_plugins ) ) {
			return $update_plugins;
		}
		// Exit if the plugin is not contained in the 'checked' array.
		if ( ! isset( $update_plugins->checked[ $this->directory . '/' . $this->slug . '.php' ] ) ) {
			return $update_plugins;
		}
		
		if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) ) {
			$update_plugins->response = array();
		}
		
		// Only set the response if the plugin has a new release.
		$this->latest_version = $this->get_latest_version();
        //if ( version_compare( $this->current_version, $this->latest_version ) ) { //if new version number is known
		//if( time() > ($update_plugins->last_checked+(60*5)) ){ //Delay the update check to avoid too much traffic
			if ( $this->is_latest_version_available() ) {
				$update_plugins->response[ $this->directory . '/' . $this->slug . '.php'] = $this->get_plugin_response_data();
			}
		//}
		
		return $update_plugins;
	}

	/**
	 * Gets the plugin response data to use if there is a new version of the plugin.
	 *
	 * @return object The response object providing the plugin details: slug, version, url, and package location.
	 */
	protected function get_plugin_response_data()
	{
		return (object) array(
			'slug'         => $this->slug,
			'new_version'  => '.', //Avoiding displaying new version. 
			'url'          => $this->get_url(),
			'package'      => $this->get_package_url(),
		);
	}
	
	function filter_upgrader_post_install(  $response = null, $hook_extra = null, $result = null ){
		update_option("_last_updated_".$this->slug, time());
		update_option( $this->slug."_update_available", 'no');
		return $response;
	}
	
	function get_last_update_time(){
		$last_updated = get_option("_last_updated_".$this->slug);
		return $last_updated;
	}
	
	protected function get_current_version(){
		return $this->current_version;
	}
	
	//Change the default wordpress update message to avoid displaying version number. 
	function change_plugin_update_message( $data, $response ) {
		printf(
			'<div class="update-message"><p><strong>%s</strong></p></div>',
			__( 'New Update is available, please update plugin now.', 'text-domain' )
		);
	}
	
	protected function get_directory(){
		return $this->directory;
	}
	
	protected function get_slug(){
		return $this->slug;
	}
	
	protected function get_gitusername(){
		return $this->gitusername;
	}
	
	protected function get_gitrepo(){
		return $this->gitrepo;
	}
	
	protected function get_gitbranch(){
		return $this->gitbranch;
	}
}

?>