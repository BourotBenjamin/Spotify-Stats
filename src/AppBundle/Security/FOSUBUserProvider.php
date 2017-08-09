<?php
/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AppBundle\Security;
use AppBundle\Entity\User;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
/**
 * Class providing a bridge to use the FOSUB user provider with HWIOAuth.
 *
 * In order to use the class as a connector, the appropriate setters for the
 * property mapping should be available.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class FOSUBUserProvider implements UserProviderInterface, AccountConnectorInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;
    /**
     * @var array
     */
    protected $properties = array(
        'identifier' => 'id',
    );
    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    private $tokenStorage;
    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager fOSUB user provider
     * @param array                $properties  property mapping
     */
    public function __construct(UserManagerInterface $userManager, array $properties, TokenStorage $tokenStorage)
    {
        $this->userManager = $userManager;
        $this->properties = array_merge($this->properties, $properties);
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->tokenStorage = $tokenStorage;
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->loadUserByUsername($username);
        }
        return $this->userManager->findUserByUsername($username);
    }
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        if (null === $username ) {
            throw new AccountNotLinkedException(sprintf("User '%s' not found.", $username));
        }
        $resourceOwnerName = $response->getResourceOwner()->getName();
        $propertyName = $this->getProperty($response);
        $setterName = 'set'.ucfirst($propertyName);
        $accessTokenSetterName = 'set'.ucfirst($resourceOwnerName).'AccessToken';
        $refreshTokenSetterName = 'set'.ucfirst($resourceOwnerName).'RefreshToken';
        $secretTokenSetterName = 'set'.ucfirst($resourceOwnerName).'SecretToken';
        $setterName = 'set'.ucfirst($propertyName);
        $email = empty($response->getEmail())?($username."@".$resourceOwnerName.".com"):$response->getEmail();
        $user = null;
        if(is_object($token =  $this->tokenStorage->getToken()))
            $user = $token->getUser();
        if(!is_object($user))
            $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
        if(!is_object($user))
            $user = $this->userManager->findUserBy(array("email" => $email));
        if (null === $user) {
            $user = new User();
            $user
                ->setEmail($email)
                ->setUsername(empty($response->getRealName())?$username:$response->getRealName())
                ->addRole("ROLE_USER")
                ->setEnabled(true)
                ->setLocked(false)
                ->setPlainPassword(bin2hex(openssl_random_pseudo_bytes(32)));
            ;
        }
        if(is_object($user)) {
            $user->$setterName($username);
            if($resourceOwnerName == "spotify")
                $user
                    ->$accessTokenSetterName($response->getOAuthToken()->getAccessToken())
                    ->$refreshTokenSetterName($response->getOAuthToken()->getRefreshToken());
            else
                $user
                    ->$accessTokenSetterName($response->getOAuthToken()->getAccessToken())
                    ->$secretTokenSetterName($response->getOAuthToken()->getTokenSecret());
            $this->userManager->updateUser($user);
        }
        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }
        $property = $this->getProperty($response);
        $username = $response->getUsername();
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $this->disconnect($previousUser, $response);
        }
        if ($this->accessor->isWritable($user, $property)) {
            $this->accessor->setValue($user, $property, $username);
        } else {
            throw new \RuntimeException(sprintf('Could not determine access type for property "%s".', $property));
        }
        $this->userManager->updateUser($user);
    }
    /**
     * Disconnects a user.
     *
     * @param UserInterface         $user
     * @param UserResponseInterface $response
     */
    public function disconnect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $this->accessor->setValue($user, $property, null);
        $this->userManager->updateUser($user);
    }
    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        // Compatibility with FOSUserBundle < 2.0
        if (class_exists('FOS\UserBundle\Form\Handler\RegistrationFormHandler')) {
            return $this->userManager->refreshUser($user);
        }
        $identifier = $this->properties['identifier'];
        if (!$user instanceof User || !$this->accessor->isReadable($user, $identifier)) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }
        $userId = $this->accessor->getValue($user, $identifier);
        if (null === $user = $this->userManager->findUserBy(array($identifier => $userId))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
        }
        return $user;
    }
    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();
        return $userClass === $class || is_subclass_of($class, $userClass);
    }
    /**
     * Gets the property for the response.
     *
     * @param UserResponseInterface $response
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }
        return $this->properties[$resourceOwnerName];
    }
}