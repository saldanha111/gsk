<?php

/*
 * Custom LDAP provider derived form the Symfony package Security Core LDAP auth provider.
 */

namespace Nononsense\UserBundle\Security\Authentication\Provider;

use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nononsense\UserBundle\Entity\Users;

/**
 * Based on Symfony's LdapBindAuthenticationProvider Class.
 */
class FormLdapBindAuthProvider extends UserAuthenticationProvider
{
    private $userProvider;
    private $ldap;
    private $dnString;

    /**
     * @param UserProviderInterface $userProvider               A UserProvider
     * @param UserCheckerInterface  $userChecker                A UserChecker
     * @param string                $providerKey                The provider key
     * @param LdapClientInterface   $ldap                       An Ldap client
     * @param string                $dnString                   A string used to create the bind DN
     * @param bool                  $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, LdapClientInterface $ldap, $dnString = '{username}', ValidatorInterface $validator, $hideUserNotFoundExceptions = true, EntityManager $entityManager)
    {
        parent::__construct($userChecker, $providerKey, $hideUserNotFoundExceptions);

        $this->userProvider = $userProvider;
        $this->ldap = $ldap;
        $this->dnString = $dnString;
        $this->em = $entityManager;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        if ('NONE_PROVIDED' === $username) {
            throw new UsernameNotFoundException('Username can not be null');
        }

        return $this->userProvider->loadUserByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $username = $token->getUsername();
        $password = $token->getCredentials();

        $validEmail = $this->validator->validate(
            $username,
            new Assert\Email()
        );

        if (0 === count($validEmail)) {

            $user = $this->em->getRepository(Users::class)->findOneBy(array('email' => $username));

            if ($user) {
                $username = $user->getUsername();
            }
        }

        if ('' === (string) $password) {
            throw new BadCredentialsException('The presented password must not be empty.');
        }

        try {
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_DN);
            $dn = str_replace('{username}', $username, $this->dnString);

            $this->ldap->bind($dn, $password);
        } catch (ConnectionException $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        }
    }
}
