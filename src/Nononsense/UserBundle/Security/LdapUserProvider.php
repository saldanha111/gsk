<?php

/*
 * Custom LDAP provider.
 */

namespace Nononsense\UserBundle\Security;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\User\User;
use Nononsense\UserBundle\Entity\Users as NononsenseUser;
use Nononsense\UserBundle\Entity\UsersSection as NononsenseUsersSection;
use Nononsense\UserBundle\Entity\Sections;
use Nononsense\GroupBundle\Entity\GroupUsers as NononsenseGroupUsers;
use Nononsense\UtilsBundle\Classes\SOAP\SOAPConnect as auth;

/**
 * Custom LDAP provider.
 */
class LdapUserProvider implements UserProviderInterface 
{
    private $ldap;
    private $container;
    private $request;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;
    private $sectors;

    /**
     * @param LdapClientInterface $ldap
     * @param Container           $container
     * @param string              $baseDn
     * @param string              $searchDn
     * @param string              $searchPassword
     * @param array               $defaultRoles
     * @param string              $uidKey
     * @param string              $filter
     */
    public function __construct(LdapClientInterface $ldap, Container $container, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})')
    {
        $this->_debug = true;
        $this->ldap = $ldap;
        $this->container = $container;
        $this->request = $this->container->get('request');
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
        $this->sectors = array();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {   
        
        $em = $this->container->get('doctrine')->getManager();

        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->find($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }
        
        //we have to set inactive users that are not found in the LDAP and that
        //their login is not by email
        $emailAuth = strpos($this->request->get('_username'), '@');
        if ($emailAuth === false && (!$search || $search['count'] == 0)){
            $user = $em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('username' => $this->request->get('_username')));
            //if the user is not found in the LDAP
            if(!empty($user)){
                $user->setIsActive(0);
                $em->persist($user);
                $em->flush();
            }
        } else if ($emailAuth !== false) {
            $user = $em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('email' => $this->request->get('_username')));
            if (!empty($user)){
                $roles = $user->getRoles();
                $revoke = true;
                foreach ($roles as $key => $value){
                    if ($value == 'ROLE_ADMIN' || $value == 'ROLE_SUPER_ADMIN'){
                        $revoke = false;
                        break;
                    }
                }
                if ($revoke){
                    $user->setIsActive(0);
                    $em->persist($user);
                    $em->flush();
                    throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
                }
            }
        }
        
        if (!$search || $search['count'] == 0) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($search['count'] > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        
        $user = $search[0];
        return $this->loadUser($username, $user);
    }

    public function loadUser($username, $user)
    {
        if($this->_debug){
            $fp = fopen(__DIR__ . '/data_' . $username . '.txt', 'w+');
        }
        //get the available user data in the LDAP
        $userData = array();
        //email
        if(isset($user['mail']) && $user['mail']['count'] > 0){
            $userData['email'] = $user['mail'][0];
        } 
        //name
        if(isset($user['displayname']) && $user['displayname']['count'] > 0){
            $userData['name'] = $user['displayname'][0];
        } 
        // avoid using LDAP
        if ($this->container->hasParameter('ldap_enabled')){
            $ldapEnabled = $this->container->getParameter('ldap_enabled');
        } else {
            $ldapEnabled = 0;
        }
        
        if ($ldapEnabled == 0) {
            throw new \Exception('LDAP is disabled');
        }

        $usernameParam = $this->request->get('_username');
        $passwordParam = trim($this->request->get('_password'));
		if($this->_debug){
            fwrite($fp, 'username:' . $usernameParam . PHP_EOL);
        }

        // throw an exception if the password isn't correct
        $this->ldap->bind($user['dn'], $passwordParam);
		
		if($this->_debug){
            fwrite($fp, 'password correcto' . PHP_EOL);
        }
        
        $roles = $this->defaultRoles;

        $em = $this->container->get('doctrine')->getManager();
        $user = $em->getRepository('NononsenseUserBundle:Users')->findOneBy(array('username' => $usernameParam));
        
        //check if the user needs to be preregisted
        if ($this->container->hasParameter('ldap_preregistered')
            && $this->container->getParameter('ldap_preregistered')){
            $preregistered = true;
            $tok = 'requiere preregistro';
        } else {
            $preregistered = false;
            $tok = 'crea usuario si no existe';
        }
        if($this->_debug){
            fwrite($fp, 'preregistered:' . $tok . PHP_EOL);
            fwrite($fp, 'username:' . $usernameParam . PHP_EOL);
            //Check that the user is authorized via Web Service
            //get Profile

        }
        $emailAuth = strpos($usernameParam, '@');


        if ($preregistered && !$user){
            if($this->_debug){
                fwrite($fp, 'Exige preregistered' . PHP_EOL);
            }
            return false;
        } else if ($user) {
            if($this->_debug){
                fwrite($fp, 'Encontro el usuario' . PHP_EOL);
            }
            // regenerate the password otherwise the system may kick you
            //out if it has changed
            $generator = new SecureRandom();
            $user->setSalt(base64_encode($generator->nextBytes(10)));
            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($passwordParam, $user->getSalt());
            $user->setPassword($password);
            if (isset($userData['name'])){
                $user->setName($userData['name']);
            } 
            if (isset($userData['email'])){
                $user->setEmail($userData['email']);
            }
            $em->persist($user);
            $em->flush();
        } else if (!$user && $emailAuth === false ) {
            if($this->_debug){
                fwrite($fp, 'Entra en la creación de usuario' . PHP_EOL);
            }
            // the user doesn't exist, create and return it
            $user = new NononsenseUser();
            $user->setUsername($usernameParam);
            if (isset($userData['name'])){
                $user->setName($userData['name']);
            } else {
                $user->setName($usernameParam);
            }

            $width = $this->container->getParameter('avatar_width');
            $height = $this->container->getParameter('avatar_height');
            $size = array('width' => $width, 'height' => $height);
            $image = \Nononsense\UtilsBundle\Classes\Utils::generateColoredPNG($size);
            $user->setIsActive(true);
            $user->setPhoto($image);
            $user->setDescription($this->container->get('translator')->trans('<p>Insert <strong>here</strong> the user description.</p>'));

            foreach ($roles as $role) {
                $role = $em->getRepository('NononsenseUserBundle:Roles')->findOneByName($role);
                $user->addRole($role);
            }
            
            // generate a password
            $generator = new SecureRandom();
            $user->setSalt(base64_encode($generator->nextBytes(10)));
            $factory = $this->container->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($passwordParam, $user->getSalt());
            $user->setPassword($password);
            if (isset($userData['email'])){
                $user->setEmail($userData['email']);
            } else {
                //email con lDAP credentials
                $user->setEmail($usernameParam );
            }
            $em->persist($user);
            $em->flush();
            //grab the id
            $id = $user->getId();
            //save the uploaded image as a  medium size image and a thumb
            $webPath = $this->container->getParameter('user_img_dir');
            $absolutePath = __DIR__ .'/../../../../web/' . $webPath;
            $imagePath = $absolutePath . 'user_' . $id . '.jpg';
            $img = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), $width, $height, 90, $imagePath);
            $thumbPath = $absolutePath . 'thumb_' . $id . '.jpg';
            $thumb = \Nononsense\UtilsBundle\Classes\Utils::resize2JPG($user->getPhoto(), 140, 140, 100, $thumbPath);
        }
        if($this->_debug){
            fwrite($fp, 'termino loop usuario' . PHP_EOL);
        }
        if($this->_debug){
            fclose($fp);
        }
        // return the user
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return new User($user->getUsername(), null, $user->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
    
    private function _updateProfile($user, $groups, $sections)
    {
        $em = $this->container->get('doctrine')->getManager();
        //GROUPS
        //If there is at least one group we make sure that the user is active
        //because if a previous connection failed it was set to inactive
        $user->setIsActive(true);
        $em->persist($user);

        //We remove the current manyToMany relationship among
        //this user and groups
        $gus = $em->getRepository('NononsenseGroupBundle:GroupUsers')
                 ->findBy(array('user' => $user));
        //delete these entries
        foreach ($gus as $gu){
            $em->remove($gu);
        }
        foreach ($groups as $group){
            //now detect the group that we are looking for
            $usg = $em->getRepository('NononsenseGroupBundle:Groups')
                 ->findOneBy(array('codigo' => $group));
            //create a user group entity
            if (!empty($usg)){
                $ug = new NononsenseGroupUsers();
                $ug->setUser($user);
                $ug->setGroup($usg);
                $ug->setType('member');
                $em->persist($ug);
            }
        }

        
        //FLUSH
        $em->flush();
    }
    
}
