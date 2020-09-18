<?php

namespace App\Security;

use App\Exception\CallException;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\AppUserModel;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class AppUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername(string $email): ?AppUserModel
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $username argument may not actually be a username:
        // it is whatever value is being returned by the getUsername()
        // method in your User class.

        return $this->getUserByEmail($email);
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
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof AppUserModel) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        return $this->getUserById($user->getId());
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class)
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

    private function getUserByEmail($email)
    {
        $query = [
            'q' => 'email:"'.$email.'"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = new AppUserModel();
                $user->setId($row['_id']);
                $user->setEmail($row['_source']['email']);
                $user->setPassword($row['_source']['password']);
                $user->setRoles($row['_source']['roles']);
                if (true === isset($row['_source']['created_at']) && '' != $row['_source']['created_at']) {
                    $user->setCreatedAt(new \Datetime($row['_source']['created_at']));
                }
                return $user;
            }
        }

        return null;
    }

    private function getUserById($id)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-users/_doc/'.$id);
        $callResponse = $this->callManager->call($callRequest);
        $row = $callResponse->getContent();

        if ($row) {
            $user = new AppUserModel();
            $user->setId($row['_id']);
            $user->setEmail($row['_source']['email']);
            $user->setPassword($row['_source']['password']);
            $user->setRoles($row['_source']['roles']);
            if (true === isset($row['_source']['created_at']) && '' != $row['_source']['created_at']) {
                $user->setCreatedAt(new \Datetime($row['_source']['created_at']));
            }
            return $user;
        }

        return null;
    }
}
