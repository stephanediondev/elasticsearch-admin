<?php

namespace App\Security;

use App\Exception\CallException;
use App\Manager\AppUserManager;
use App\Model\AppUserModel;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class AppUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    protected AppUserManager $appUserManager;

    public function __construct(AppUserManager $appUserManager)
    {
        $this->appUserManager = $appUserManager;
    }

    public function loadUserByIdentifier(string $identifier): ?AppUserModel
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in your User class.

        return $this->appUserManager->getByEmail($identifier);
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return AppUserModel
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $email): ?AppUserModel
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in your User class.

        return $this->appUserManager->getByEmail($email);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @return AppUserModel
     */
    public function refreshUser(UserInterface $user): ?AppUserModel
    {
        if (!$user instanceof AppUserModel) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        return $this->appUserManager->getById($user->getId());
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return AppUserModel::class === $class;
    }

    /**
     * Upgrades the encoded password of a user, typically for using a better hash algorithm.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // When encoded passwords are in use, this method should:
        // 1. persist the new password in the user storage
        // 2. update the $user object with $user->setPassword($newEncodedPassword);
    }
}
