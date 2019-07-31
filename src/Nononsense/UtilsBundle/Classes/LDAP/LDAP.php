<?php

namespace Nononsense\UtilsBundle\Classes\LDAP;

use Symfony\Component\Ldap\LdapClient;

class LDAP
{
    
    /**
     * constructor
     *
     * 
     */
    public function __construct($container) 
    {    
        $this->_container = $container;
        
        $host = $this->_container->getParameter('ldap_host');
        $port = $this->_container->getParameter('ldap_port');
        $version = $this->_container->getParameter('ldap_version');
        $ssl = $this->_container->getParameter('ldap_ssl');
        $tls = $this->_container->getParameter('ldap_tls');
        $this->_ldap = new LdapClient($host, $port, $version, $ssl, $tls);
        
    }
    
    /**
     * makes a query to get all available user info
     *
     * @param string username
     * @param boolean raw
     * @return array 
     * @access public
     */
    public function query($username, $raw = false)
    {
        //bind
        $dn = $this->_container->getParameter('ldap_search_dn');
        $password = $this->_container->getParameter('ldap_search_password');
        $this->_ldap->bind($dn, $password);
        //Search
        $filter = $this->_container->getParameter('ldap_filter');
        $baseDn = $this->_container->getParameter('ldap_base_dn');
        $username = $this->_ldap->escape($username, '', LDAP_ESCAPE_FILTER);
        $query = str_replace('{username}', $username, $filter);
        $search = $this->_ldap->find($baseDn, $query);
        $userData = array();
        //email
        if(isset($search[0]['mail']) && $search[0]['mail']['count'] > 0){
            $userData['email'] = $search[0]['mail'][0];
        } 
        //name
        if(isset($search[0]['displayname']) && $search[0]['displayname']['count'] > 0){
            $userData['name'] = $search[0]['displayname'][0];
        } 
        if ($raw){
            return $search[0];
        } else {
            return $userData;
        }
    }
    

}
