<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MY_Session class extends the core CI_Session class.
 *
 * @author	Simon Kort <s.a.e.kort@uu.nl>
 * @version	1.0.0
 */
class MY_Session extends CI_Session {
	
    /**
     * Update an existing session
     *
     * @access    public
     * @return    void
    */
    function sess_update() {
       // skip the session update if this is an AJAX call! This is a bug in CI; see:
       // https://github.com/EllisLab/CodeIgniter/issues/154
       // http://codeigniter.com/forums/viewthread/102456/P15
       if ( !($this->CI->input->is_ajax_request()) ) {
           parent::sess_update();
       }
    }
    
    /**
     * Destroy the current session
     *
     * @access	public
     * @return	void
     */
    function sess_destroy()
    {
    	// Kill the session DB row
    	if ($this->sess_use_database === TRUE AND isset($this->userdata['session_id']))
    	{
    		$this->CI->db->where('session_id', $this->userdata['session_id']);
    		$this->CI->db->delete($this->sess_table_name);
    	}
    
    	// Kill the cookie
    	setcookie(
    			$this->sess_cookie_name,
    			addslashes(serialize(array())),
    			($this->now - 31500000),
    			$this->cookie_path,
    			$this->cookie_domain,
    			TRUE,
    			TRUE
    	);
    }
    
    /**
     * Write the session cookie
     *
     * @access	public
     * @return	void
     */
    function _set_cookie($cookie_data = NULL)
    {
    	if (is_null($cookie_data))
    	{
    		$cookie_data = $this->userdata;
    	}
    
    	// Serialize the userdata for the cookie
    	$cookie_data = $this->_serialize($cookie_data);
    
    	if ($this->sess_encrypt_cookie == TRUE)
    	{
    		$cookie_data = $this->CI->encrypt->encode($cookie_data);
    	}
    	else
    	{
    		// if encryption is not used, we provide an md5 hash to prevent userside tampering
    		$cookie_data = $cookie_data.md5($cookie_data.$this->encryption_key);
    	}
    
    	$expire = ($this->sess_expire_on_close === TRUE) ? 0 : $this->sess_expiration + time();
    
    	// Set the cookie
    	setcookie(
    			$this->sess_cookie_name,
    			$cookie_data,
    			$expire,
    			$this->cookie_path,
    			$this->cookie_domain,
    			$this->cookie_secure,
    			TRUE
    	); // Added TRUE flag for httponly
    }    
}