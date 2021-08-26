<?php

/*
 * Custom LDAP provider derived form the Symfony package Security Core LDAP user provider.
 */

namespace Nononsense\UserBundle\Security\Authentication\Provider;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\DependencyInjection\Container;
use Nononsense\UserBundle\Entity\Users;
use Nononsense\UserBundle\Entity\Roles;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Based on Symfony's LdapUserProvider Class.
 */
class FormLdapUserProvider implements UserProviderInterface
{
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;

    /**
     * @param LdapClientInterface $ldap
     * @param string              $baseDn
     * @param string              $searchDn
     * @param string              $searchPassword
     * @param array               $defaultRoles
     * @param string              $uidKey
     * @param string              $filter
     */
    public function __construct(LdapClientInterface $ldap, Container $container, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName', $filter = '({uid_key}={username})')
    {
        $this->ldap = $ldap;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {

        $em = $this->container->get('doctrine')->getManager();

        // $user = $em->getRepository(Users::class)->findOneBy(array('username' => $username));

        // if (!$user) {
        //     throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        // }

        $validEmail = $this->container->get('validator')->validate(
            $username,
            new Assert\Email()
        );

        if (0 === count($validEmail)) {

            $user = $em->getRepository(Users::class)->findOneBy(array('email' => $username));

            if ($user) {
                $username = $user->getUsername();
            }
        }

        $this->searchPassword = $this->container->get('request')->get('_password');
        $this->searchDn = str_replace('{username}', $username, $this->searchDn);

        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->find($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        if (!$search) {
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
        $em = $this->container->get('doctrine')->getManager();

        $newUser = $em->getRepository(Users::class)->findOneBy(array('username' => $username));

        if (!$newUser) {

             throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
            // $newUser = new Users();

            // $role = $em->getRepository(Roles::class)->findOneBy(array('name' => 'ROLE_USER'));
            // $newUser->addRole($role);
        }
        
        $newUser->setUsername($user['cn'][0]);
        $newUser->setName($user['displayname'][0]);
        $newUser->setEmail($user['mail'][0]);
        $newUser->setIsActive(true);

        if (isset($user['description'][0]) && $user['description'][0]) {
            $newUser->setDescription($user['description'][0]);
        }else{
            $newUser->setDescription('');
        }

        if (isset($user['mobile'][0]) && $user['mobile'][0]) {
            $newUser->setPhone($user['mobile'][0]);
        }
        
        if (isset($user['title'][0]) && $user['title'][0]) {
           $newUser->setPosition($user['title'][0]);
        }
        
        // $newUser->seccion($user['department'][0]);
        // $newUser->area($user['businesscategory'][0]);

        $generator = new SecureRandom();
        $newUser->setSalt(base64_encode($generator->nextBytes(10)));
        $factory = $this->container->get('security.encoder_factory');
        $encoder = $factory->getEncoder($newUser);
        $password = $encoder->encodePassword($this->container->get('request')->get('_password'), $newUser->getSalt());
        $newUser->setPassword($password);

        $em->persist($newUser);
        $em->flush();
        

        //$password = isset($user['userpassword']) ? $user['userpassword'] : null;

        //$roles = $this->defaultRoles;

        return $newUser;

        //return new User($username, $password, $roles);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return new User($user->getUsername(), null, $user->getRoles());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }
}
