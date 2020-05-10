<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\OTP\OTPService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * On oblige à changer le mdp si expiré
 * Class ExpiredPasswordSubscriber
 *
 * @package App\EventSubscriber
 */
class ExpiredPasswordSubscriber implements EventSubscriberInterface
{
    private const FIREWALL_NAME = 'main';
    private const ROUTE_EXPIRED_PASSWORD = 'expired_password';
    private const ROLE_EXPIRED_PASSWORD_SUCCEED = 'EXPIRED_PASSWORD_SUCCEED';
    private const PASSWORD_EXPIRATION_DAYS = 90;

    /** @var RouterInterface  */
    private $router;
    /** @var TokenStorageInterface  */
    private $tokenStorage;

    /**
     * ExpiredPasswordSubscriber constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param RouterInterface $router
     */
    public function __construct(TokenStorageInterface $tokenStorage, RouterInterface $router)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', -15]],
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (in_array($event->getRequest()->attributes->get('_route'), [self::ROUTE_EXPIRED_PASSWORD, OTPService::ROUTE_TWO_FACTOR], true)) {
            return;
        }

        $currentToken = $this->tokenStorage->getToken();

        if ($currentToken instanceof PostAuthenticationGuardToken
           && self::FIREWALL_NAME === $currentToken->getProviderKey()
           && !in_array(self::ROLE_EXPIRED_PASSWORD_SUCCEED, $currentToken->getRoleNames(), true)
        ) {
            // check if expired password
            /** @var User $currentUser */
            $currentUser = $currentToken->getUser();
            $now = new \DateTime();
            $passwordUpdatedAt = $currentUser->getPasswordUpdatedAt();
            $diff = $now->diff($passwordUpdatedAt);

            if ($diff->format('%a') < self::PASSWORD_EXPIRATION_DAYS) {
                $this->addExpiredPasswordRole($this->tokenStorage, $event->getRequest()->getSession());
            } else {
                $response = new RedirectResponse($this->router->generate(self::ROUTE_EXPIRED_PASSWORD));
                $event->setResponse($response);
            }
        }
    }

    private function addExpiredPasswordRole(TokenStorageInterface $tokenStorage, SessionInterface $session): void
    {
        /** @var PostAuthenticationGuardToken $currentToken */
        $currentToken = $tokenStorage->getToken();
        $roles = array_merge($currentToken->getRoleNames(), [self::ROLE_EXPIRED_PASSWORD_SUCCEED]);
        $newToken = new PostAuthenticationGuardToken($currentToken->getUser(), $currentToken->getProviderKey(), $roles);
        $tokenStorage->setToken($newToken);
        $session->set('_security_' . $currentToken->getProviderKey(), serialize($newToken));
    }
}
